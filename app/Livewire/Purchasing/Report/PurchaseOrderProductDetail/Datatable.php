<?php

namespace App\Livewire\Purchasing\Report\PurchaseOrderProductDetail;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Settings\SettingLogistic;
use App\Settings\SettingPurchasing;
use App\Helpers\General\ExportHelper;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Livewire\WithDatatableExport;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Report\Expense\ExpenseRepository;
use App\Repositories\Purchasing\Master\Supplier\SupplierRepository;
use App\Repositories\Purchasing\Report\PurchaseOrder\PurchaseOrderRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;
use App\Repositories\Purchasing\Report\PurchaseOrderProduct\PurchaseOrderProductRepository;
use App\Repositories\Purchasing\Report\PurchaseOrderProductDetail\PurchaseOrderProductDetailRepository;

class Datatable extends Component
{
    use WithDatatable, WithDatatableExport;

    public $dateStart;
    public $dateEnd;
    public $productIds = [];
    public $categoryProductIds = [];
    public $supplierIds = [];

    public $header = [];
    public $show_header = true;

    public $isInputProductCode;
    public $isInputProductExpiredDate;
    public $isInputProductBatch;

    public function onMount()
    {
        $this->dateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
    
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
        $this->isInputProductCode = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_PRODUCT_CODE);
        $this->isInputProductExpiredDate = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_PRODUCT_EXPIRED_DATE);
        $this->isInputProductBatch = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_PRODUCT_BATCH);
    }

    function datatableExportFileName(): string
    {
        return 'Laporan Pembelian Barang Detail ' . Carbon::parse($this->dateStart)->format('Y-m-d') . ' sd ' . Carbon::parse($this->dateEnd)->format('Y-m-d');
    }

    function datatableExportFilter(): array
    {
        $supplierIds = collect($this->supplierIds)->map(function ($id) {
            return SupplierRepository::find($id)->name;
        })->toArray();
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
            'Supplier' => implode(" , ", $supplierIds),
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

        return [7 + $colspan, 8 + $colspan, 9 + $colspan, 10 + $colspan];
    }

    public function getColumns(): array
    {
        $columns = [
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
                'name' => 'Supplier',
                'render' => function($item)
                {
                    return $item->supplier_name;
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
                'render' => function($item)
                {
                    return $item->unit_detail_name;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Harga Satuan',
                'footer' => 'Total',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->price);
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
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Total',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->value);
                }
            ]
        );

        return $columns;
    }

    public function getQuery(): Builder
    {
        return PurchaseOrderProductDetailRepository::datatable($this->search, $this->dateStart, $this->dateEnd, $this->productIds, $this->categoryProductIds, $this->supplierIds);
    }

    public function getView(): string
    {
        return 'livewire.purchasing.report.purchase-order-product-detail.datatable';
    }
}
