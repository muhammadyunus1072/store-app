<?php

namespace App\Livewire\Logistic\Report\CurrentStockDetailWarehouse;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Settings\SettingLogistic;
use App\Helpers\General\ExportHelper;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\Core\UserStateHandler;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Livewire\WithDatatableExport;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;
use App\Repositories\Logistic\Report\CurrentStock\CurrentStockRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;
use App\Repositories\Logistic\Report\CurrentStockWarehouse\CurrentStockWarehouseRepository;
use App\Repositories\Logistic\Report\CurrentStockDetailWarehouse\CurrentStockDetailWarehouseRepository;

class Datatable extends Component
{
    use WithDatatable, WithDatatableExport;

    public $dateStart;
    public $dateEnd;
    public $productIds = [];
    public $categoryProductIds = [];

    // Helpers
    public $isMultipleCompany = false;

    public $companies = [];
    public $warehouses = [];

    public $warehouseId;
    public $warehouseText;

    public $companyId;
    public $companyText;

    public $isInputProductCode;
    public $isInputProductExpiredDate;
    public $isInputProductBatch;
    
    public function onMount()
    {
        $this->dateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->loadUserState();
        $this->loadSetting();
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
    
    public function loadSetting()
    {
        $this->isInputProductCode = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_CODE);
        $this->isInputProductExpiredDate = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_EXPIRED_DATE);
        $this->isInputProductBatch = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_BATCH);
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
    public function datatableExportPaperOption()
    {
        return [
            'size' => 'legal',
            'orientation' => 'landscape',
        ];
    }

    function datatableExportFileName(): string
    {
        return 'Laporan Stok Detail Gudang ' . Carbon::parse($this->dateStart)->format('Y-m-d') . ' sd ' . Carbon::parse($this->dateEnd)->format('Y-m-d');
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

        $colspan = 0;
        if($this->isInputProductCode)
        {
            $colspan ++;
        }
        if($this->isInputProductExpiredDate)
        {
            $colspan ++;
        }
        if($this->isInputProductBatch)
        {
            $colspan ++;
        }
        return [3 + $colspan, 4 + $colspan, 5 + $colspan, 6 + $colspan, 7 + $colspan, 8 + $colspan, 9 + $colspan, 10 + $colspan, 11 + $colspan, 12 + $colspan, 13 + $colspan, 14 + $colspan, 15 + $colspan];
    }
    
    public function getColumns(): array
    {
        
        $columns = [
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'No',
                'render' => function ($item, $index) {
                    return $index + 1;
                }
            ],
            [
                'key' => 'name',
                'name' => 'Nama Produk',
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Harga',
                'render' => function ($item) {
                    return NumberFormatter::format($item->price);
                }
            ],
        ];

        if($this->isInputProductCode)
        {
            $columns[] =
                [
                    'sortable' => false,
                    'searchable' => false,
                    'name' => 'Kode',
                    'render' => function ($item) {
                        return $item->code;
                    }
                ];
        }
        if($this->isInputProductBatch)
        {
            $columns[] =
                [
                    'sortable' => false,
                    'searchable' => false,
                    'name' => 'Batch',
                    'render' => function ($item) {
                        return $item->batch;
                    }
                ];

        }
        if($this->isInputProductExpiredDate)
        {
            $columns[] =
                [
                    'sortable' => false,
                    'searchable' => false,
                    'name' => 'Expired Date',
                    'render' => function ($item) {
                        return $item->expired_date;
                    }
                ];

        }

        array_push(
            $columns,    
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
                'name' => 'Stok Awal',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->stock_quantity - $item->quantity_stock_expense - $item->quantity_purchase_order);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Pembelian',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->quantity_purchase_order);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Tranfer Masuk',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->incoming_tranfer_quantity);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Tranfer Keluar',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->outgoing_tranfer_quantity * -1);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Pengeluaran',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->quantity_stock_expense * -1);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Stok Akhir',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->stock_quantity);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Awal',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->stock_value - $item->value_stock_expense - $item->value_purchase_order);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Pembelian',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->value_purchase_order);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Tranfer Masuk',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->incoming_tranfer_value);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Tranfer Keluar',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->outgoing_tranfer_value * -1);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Pengeluaran',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->value_stock_expense * -1);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Akhir',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->stock_value);
                }
            ],
        );
        return $columns;
    }

    public function getQuery(): Builder
    {
        return CurrentStockDetailWarehouseRepository::datatable($this->search, $this->dateStart, $this->dateEnd, $this->productIds, $this->categoryProductIds, $this->warehouseId);
    }
    
    public function getView(): string
    {
        return 'livewire.logistic.report.current-stock-detail-warehouse.datatable';
    }
}
