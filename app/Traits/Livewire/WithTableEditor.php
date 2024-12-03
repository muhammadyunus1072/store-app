<?php

namespace App\Traits\Livewire;

use Exception;
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
    public $sortBy = 'id';
    public $sortDirection = 'asc';
    public $loading = false;
    public $showKeywordFilter = true;
    public $showSelectPageLength = true;
    public $tableName = [];
    public $allColumns = [];
    public $tableData = [];
    
    public $row_updates = [];
    public $updatedKey;
    public $isSeluruh = false;
    public $rangeStart = 0;
    public $rangeEnd = 0;
    public $value;

    abstract protected static function className() : string;

    public function onMount() {}

    public function mount()
    {
        $columns = $this->columns();
        $this->loading = true;
        if ('' == $this->sortBy && count($columns) > 0) {
            foreach ($columns as $key => $col) {
                if (!isset($col['sortable']) || $col['sortable']) {
                    $this->sortBy = $key;
                    break;
                }
            }
        }

        $this->tableName = app(static::className())->getTable();
        $this->allColumns = Schema::getColumnListing($this->tableName);
        $this->tableData = $this->getTableData($this->datatableGetProcessedQuery()->get()->keyBy('id')->toArray());
        $this->onMount();
    }

    public function getTableData($data)
    {
        return $data;
    }

    public function get(): array
    {
        return [
            [
                'key' => 'name',
                'name' => 'Golongan',
                'render' => function ($item) {
                    return $item->name;
                }
            ],
        ];
    }

    public function updatingSearch()
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
        $sortBy = $this->sortBy;
        $sortDirection = $this->sortDirection;
        
        $columnsToExclude = collect($columns)
            ->filter(fn($col) => !$col['show'])
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
            $this->row_updates[$elements[1]][$elements[2]] = $value;
        }
    }

    public function getColumns()
    {
        $allColumns = [];
        $columns = $this->columns();
        $excludeColumns = $this->excludeColumns();
        
        $allColumns[] = [
            'name' => 'Nomor',
            'sortable' => false,
            'searchable' => false,
            'render' => function($item, $name, $index)
            {
                return $index + 1;
            }
        ];
        foreach ($this->allColumns as $key => $value) {
            
            if (!isset($excludeColumns[$value]) && (!isset($columns[$value]['show']) || $columns[$value]['show'])) {

                $allColumns[$value] = isset($columns[$value]) ? $columns[$value] : [
                    'render' => function($item, $name)
                    {
                        $html = "<input type='text' class='form-control' wire:key=\"$name"."_"."$item->id\" wire:model.live=\"tableData.$item->id.".$name."\"/> ";
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
                if (isset($this->row_updates[$item->id])) {
                    foreach ($this->row_updates[$item->id] as $key => $value) {
                        if ($item->$key != $value) {
                            $action .= "<button type=\"button\" class=\"btn btn-success btn-sm\" wire:click=\"save($item->id)\">Simpan</button>";
                            break;
                        }
                    }
                }

                return $action;
            }
        ];
       return $allColumns;
    }

    public function save($id)
    {
        try {
            DB::beginTransaction();
            
            $obj = app(static::className())->find($id);
            foreach ($this->tableData[$id] as $key => $value) {
                $obj->{$key} = $value;
            }
            
            $obj->save();

            DB::commit();
            Alert::success($this, "Berhasil", "Data Berhasil Disimpan");

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

    public function excludeColumns(): array
    {
        return [
            'created_by' => [
                'show' => false,
            ],
            'created_at' => [
                'show' => false,
            ],
            'updated_at' => [
                'show' => false,
            ],
            'updated_by' => [
                'show' => false,
            ],
            'deleted_at' => [
                'show' => false,
            ],
            'deleted_by' => [
                'show' => false,
            ],
        ];
    }

    public function showData($key)
    {
        $this->updatedKey = $key;
    }
}
