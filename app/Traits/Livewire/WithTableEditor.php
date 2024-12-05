<?php

namespace App\Traits\Livewire;

use Exception;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Helpers\General\Alert;
use App\Models\Finance\Master\Tax;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait WithTableEditor
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $lengthOptions = [10, 25, 50, 100];
    public $length = 10;
    public $search;
    public $searches = [];
    public $sortBy = 'id';
    public $sortDirection = 'asc';
    public $loading = false;
    public $showKeywordFilter = true;
    public $showSelectPageLength = true;
    public $tableName = [];
    public $allColumns = [];
    public $tableData = [];
    public $newData = [];
    public $originalData = [];
    
    public $row_updates = [];
    public $updatedKey;
    public $isSeluruh = false;
    public $rangeStart = 0;
    public $rangeEnd = 0;
    public $value;

    // Delete Dialog
    public $targetDeleteId;

    abstract protected static function className() : string;

    public function onMount() 
    {
    }

    public function getTableData()
    {
        $this->tableData = $this->datatableGetProcessedQuery()->get()->keyBy('id')->toArray();
    }

    public function mount()
    {
        $this->tableName = app(static::className())->getTable();
        $this->allColumns = Schema::getColumnListing($this->tableName);
        $this->getTableData();
        $this->originalData = $this->tableData;
        $this->loading = true;
        $columns = $this->getColumns();
        if ('' == $this->sortBy && count($columns) > 0) {
            foreach ($columns as $key => $col) {
                if (!isset($col['sortable']) || $col['sortable']) {
                    $this->sortBy = $key;
                    break;
                }
            }
        } 
        
        foreach ($columns as $key => $col) {
            if(!is_numeric($key) && (!isset($col['searchable']) || $col['searchable'] == true))
            {
                $this->searches[$key] = isset($col['searchDefault']) ? $col['searchDefault'] : null;
            }
        }
        $this->onMount();
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingSearches()
    {
        $this->resetPage();
    }

    public function datatableSort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = 'asc' === $this->sortDirection
                ? 'desc'
                : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortBy = $field;
    }

    public function datatableGetProcessedQuery()
    { 
        $columns = $this->columns();
        $search = $this->search;
        $searches = $this->searches;
        $sortBy = $this->sortBy;
        $sortDirection = $this->sortDirection;
        
        $columnsToExclude = collect($columns)
            ->filter(fn($col) => isset($col['show']) && !$col['show'])
            ->pluck('name')
            ->toArray();

        $query = Tax::query();

        if (!empty($columnsToExclude)) {
            $columnsToSelect = array_diff($this->allColumns, array_keys($columnsToExclude));

            $query->select($columnsToSelect);
        }

        $query->when($search, function ($query) use ($search, $columns) {
            $query->where(function ($query) use ($columns, $search) {
                foreach ($this->getColumns() as $key => $col) {
                    if (
                        (!isset($col['searchable']) || (isset($col['searchable']) && $col['searchable']))
                    ) {
                        $query->orWhere($key, env('QUERY_LIKE'), "%$search%");
                    }
                }
            });
        });

        $query->when($searches, function ($query) use ($searches, $columns) {
            $query->where(function ($query) use ($columns, $searches) {
                foreach ($this->getColumns() as $key => $col) {
                
                    if(!isset($col['searchable']) || (isset($col['searchable']) && $col['searchable']))
                    {
                        $operator = isset($col['searchOperator']) ? $col['searchOperator'] : env('QUERY_LIKE');
                        $data = isset($col['searchOperator']) ? $this->searches[$key] : "%".$this->searches[$key]."%";
                        $query->where($key, $operator, $data);
                    }
                }
            });
        });

        $query->when($sortBy, function ($query) use ($sortBy, $sortDirection) {
            $query->orderBy($sortBy, $sortDirection);
        });

        return $query;
    }

    public function datatablePaginate($query)
    {
        return $query->paginate($this->length);
    }

    public function datatableGetData()
    {
        return $this->datatablePaginate($this->datatableGetProcessedQuery());
    }

    public function updated($element, $value)
    {
        $elements = explode('.', $element);
        if ($elements[0] == 'tableData') {
            if(!is_numeric($elements[1]))
            {
                return;
            }
            $this->row_updates[$elements[1]][$elements[2]] = $value;

            $isNoUpdate = true;
            foreach($this->row_updates[$elements[1]] as $key => $item)
            {
                if($item != $this->originalData[$elements[1]][$elements[2]])
                {
                    $isNoUpdate = false;
                    break;
                }
            }

            if($isNoUpdate)
            {
                unset($this->row_updates[$elements[1]]);
            }
        }
    }

    public function getColumns()
    {
        $allColumns = [];
        $columns = $this->columns();
        
        $allColumns[] = [
            'name' => 'Nomor',
            'sortable' => false,
            'searchable' => false,
            'render' => function($item, $name, $index)
            {
                return is_numeric($index) ? $index + 1 : '-';
            }
        ];
        foreach ($this->allColumns as $key => $value) {
            
            if (!isset($excludeColumns[$value]) && (!isset($columns[$value]['show']) || $columns[$value]['show'])) {

                $allColumns[$value] = isset($columns[$value]) ? $columns[$value] : [
                    'render' => function($item, $name)
                    {
                        $id = $item['id'];
                        $html = "<input type='text' class='form-control' wire:key=\"$name"."_".$id."\" wire:model.blur=\"tableData.$id.".$name."\"/> ";
                        return $html;
                    }
                ];
                
            }
        }

        $allColumns[] = [
            'name' => 'Aksi',
            'sortable' => false,
            'searchable' => false,
            'render' => function($item, $name, $index)
            {
                $action = "";
                $id = $item['id'];
                $event = !is_numeric($id) ? 'deleteNewData' : 'showDeleteDialog';
                $action .= "<button type=\"button\" class=\"btn btn-danger btn-sm\" wire:click=\"$event('$id')\">
                        <i class='ki-duotone ki-trash fs-1'>
                            <span class='path1'></span>
                            <span class='path2'></span>
                            <span class='path3'></span>
                            <span class='path4'></span>
                            <span class='path5'></span>
                        </i>
                            Hapus</button>";

                return $action;
            }
        ];
       return $allColumns;
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if ($this->targetDeleteId == null) {
            return;
        }
        $obj = app(static::className())
        ->find($this->targetDeleteId);
        
        empty($obj) ? null : $obj->delete();
        Alert::success($this, 'Berhasil', 'Data berhasil dihapus');
    }

    #[On('on-delete-dialog-cancel')]
    public function onDialogDeleteCancel()
    {
        $this->targetDeleteId = null;
    }

    public function showDeleteDialog($id)
    {
        $this->targetDeleteId = $id;

        Alert::confirmation(
            $this,
            Alert::ICON_QUESTION,
            "Hapus Data",
            "Apakah Anda Yakin Ingin Menghapus Data Ini ?",
            "on-delete-dialog-confirm",
            "on-delete-dialog-cancel",
            "Hapus",
            "Batal",
        );
    }

    public function addData()
    {
        $columns = $this->getColumns();
        $newData = [];
        foreach($columns as $key => $value)
        {
            $newData[$key] = isset($value['default']) ? $value['default'] : null;
        }
        $id = Str::random(30);
        if(!isset($columns['id']))
        {
            $newData['id'] = $id;
        }
        $this->tableData[$id] = $newData;

    }

    public function deleteNewData($id)
    {
        unset($this->tableData[$id]);
    }

    public function save()
    {
        try {
            DB::beginTransaction();

            // CREATE
            foreach ($this->tableData as $key => $newItem) {
                if(!is_numeric($key))
                {
                    if (isset($newItem['id'])) {
                        unset($newItem['id']);
                    }
                    unset($newItem['0']);
                    unset($newItem['1']);
                    $obj = app(static::className())->newInstance();
                    $obj->forceFill($newItem);
                    $obj->save();
                }
            }            

            // // UPDATE
            $updatedRecords = app(static::className())
                ->whereIn('id', array_keys($this->row_updates))
                ->get();

            foreach ($updatedRecords as $obj) {
                if (isset($this->row_updates[$obj->id])) {
                    foreach ($this->row_updates[$obj->id] as $key => $value) {
                        $obj->{$key} = $value;
                    }

                    $obj->save();
                }
            }

            DB::commit();
            Alert::success($this, "Berhasil", "Data Berhasil Disimpan");
            $this->row_updates = [];
            $this->getTableData();

        } catch (Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    public function saveBulk()
    {
        try {
            DB::beginTransaction();
            
            $obj = app(static::className())->whereBetween('id', [$this->rangeStart, $this->rangeEnd])
            ->orderBy('id', 'asc')
            ->update([$this->updatedKey => $this->value]);

            DB::commit();
            $this->dispatch('onSuccessStore');
            $this->dispatch('refresh-page');

        } catch (Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.livewire-table-editor', [
            'data' => $this->datatableGetData(),
            'columns' => $this->getColumns(),
        ]);
    }
    public function columns(): array
    {
        return [];
    }

    public function showData($key)
    {
        $this->updatedKey = $key;
        $columns = $this->getColumns();
        
        $this->value = isset($columns[$key]['default']) ? $columns[$key]['default'] : null;
    }
}
