<?php

namespace App\Livewire\Logistic\Report\StockExpenseWarehouse;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Helpers\General\ExportHelper;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\Core\UserStateHandler;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Livewire\WithDatatableExport;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;
use App\Repositories\Logistic\Report\StockExpenseWarehouse\StockExpenseWarehouseRepository;

class Datatable extends Component
{
    use WithDatatable, WithDatatableExport;

    public $dateStart;
    public $dateEnd;
    public $productIds = [];
    public $categoryProductIds = [];

    public $header = [];
    public $show_header = true;

    // Helpers
    public $isMultipleCompany = false;

    public $companies = [];
    public $warehouses = [];

    public $warehouseId;
    public $warehouseText;

    public $companyId;
    public $companyText;

    public function onMount()
    {
        $this->dateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
      $this->loadUserState();
    }

    public function updatedSearch()
    {
        $this->dispatch('add-filter', [
            'search' => $this->search,
        ]);
    }

    #[On('add-filter')]
    public function addFilter($filter)
    {
        foreach ($filter as $key => $value) {
            $this->$key = $value;
        }        
    }
    
    public function loadUserState()
    {
        $userState = UserStateHandler::get();
        if ($this->isMultipleCompany) {
            $this->companies = $userState['companies'];
            $this->companyId = $userState['company_id'];
            $this->warehouses = $userState['warehouses'];
            $this->warehouseId = $userState['warehouse_id'];
        } else {
            $this->companyId = $userState['company_id'];
            $this->warehouses = $userState['warehouses'];
            $this->warehouseId = Crypt::decrypt($userState['warehouse_id']);
        }
    }

    function datatableExportFileName(): string
    {
        return 'Laporan Pengeluaran Gudang ' . Carbon::parse($this->dateStart)->format('Y-m-d') . ' sd ' . Carbon::parse($this->dateEnd)->format('Y-m-d');
    }

    function datatableExportFilter(): array
    {
        $productIds = collect($this->productIds)->map(function ($id) {
            return ProductRepository::find($id)->name;
        })->toArray();
        $categoryProductIds = collect($this->categoryProductIds)->map(function ($id) {
            return CategoryProductRepository::find($id)->name;
        })->toArray();
        return [
            'Tanggal Mulai' => $this->dateStart,
            'Tanggal Akhir' => $this->dateEnd,
            'Produk' => implode(" , ", $productIds),
            'Kategori Produk' => implode(" , ", $categoryProductIds),
            'Gudang' => $this->warehouseId ? WarehouseRepository::find($this->warehouseId)->name : null,
            'Kata Kunci' => $this->search,
        ];
    }

    function datatableExportEnableFooterTotal()
    {
        return [5, 6, 7];
    }
    
    public function getColumns(): array
    {
        return [
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'No',
                'render' => function($item, $index)
                {
                    return $index + 1;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Tanggal',
                'render' => function($item)
                {
                    return Carbon::parse($item->transaction_date)->translatedFormat('d F Y');
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nomor',
                'render' => function($item)
                {
                    return $item->number;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Produk',
                'render' => function($item)
                {
                    return $item->product_name;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->quantity);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Satuan',
                'footer' => 'Total',
                'render' => function($item)
                {
                    return $item->unit_detail_name;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Konversi',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->converted_quantity);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Satuan Konversi',
                'footer' => '',
                'render' => function($item)
                {
                    return $item->main_unit_detail_name;
                }
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return StockExpenseWarehouseRepository::datatable($this->search, $this->dateStart, $this->dateEnd, $this->productIds, $this->categoryProductIds, $this->warehouseId);
    }

    public function getView(): string
    {
        return 'livewire.logistic.report.stock-expense-warehouse.datatable';
    }
}
