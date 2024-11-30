<?php

namespace App\Livewire\Purchasing\Report\PurchaseOrderProductDetail;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Settings\SettingPurchasing;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Livewire\WithDatatableExport;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Purchasing\Master\Supplier\SupplierRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;
use App\Repositories\Purchasing\Report\PurchaseOrderProductDetail\PurchaseOrderProductDetailRepository;
use Illuminate\Support\Facades\Crypt;

class Datatable extends Component
{
    use WithDatatable, WithDatatableExport;

    // Setting
    public $isInputProductCode;
    public $isInputProductExpiredDate;
    public $isInputProductBatch;

    // Filter
    public $dateStart;
    public $dateEnd;
    public $productIds = [];
    public $categoryProductIds = [];
    public $supplierIds = [];

    public function onMount()
    {
        $this->loadSetting();
    }

    public function loadSetting()
    {
        $this->isInputProductCode = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_PRODUCT_CODE);
        $this->isInputProductExpiredDate = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_PRODUCT_EXPIRED_DATE);
        $this->isInputProductBatch = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_PRODUCT_BATCH);
    }

    public function updatedSearch()
    {
        $this->dispatch('on-search-updated', [
            'search' => $this->search,
        ]);
    }
    
    /*
    | WITH DATATABLE
    */
    public function getView(): string
    {
        return 'livewire.purchasing.report.purchase-order-product-detail.datatable';
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
                'sortable' => false,
                'searchable' => false,
                'name' => 'Tanggal',
                'render' => function ($item) {
                    return Carbon::parse($item->transaction_date)->translatedFormat('d F Y');
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nomor',
                'render' => function ($item) {
                    return $item->number;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Supplier',
                'render' => function ($item) {
                    return $item->supplier_name;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Produk',
                'render' => function ($item) {
                    return $item->product_name;
                }
            ],
        ];

        if ($this->isInputProductCode) {
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
        if ($this->isInputProductBatch) {
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
        if ($this->isInputProductExpiredDate) {
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
                'render' => function ($item) {
                    return NumberFormatter::format($item->quantity);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Satuan',
                'render' => function ($item) {
                    return $item->unit_detail_name;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Harga Satuan',
                'render' => function ($item) {
                    return NumberFormatter::format($item->price);
                },
                'export_footer_total' => 'Total',
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Konversi',
                'render' => function ($item) {
                    return NumberFormatter::format($item->converted_quantity);
                },
                'export_footer_total' => true,
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Satuan Konversi',
                'footer' => '',
                'render' => function ($item) {
                    return $item->main_unit_detail_name;
                },
                'export_footer_total' => ' ',
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Sebelum Pajak',
                'render' => function ($item) {
                    return NumberFormatter::format($item->value);
                },
                'export_footer_total' => true,
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Pajak',
                'render' => function ($item) {
                    $tax = ($item->tax_value) ? $item->value * $item->tax_value / 100 : 0;
                    return NumberFormatter::format($tax);
                },
                'export_footer_total' => true,
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Total',
                'render' => function ($item) {
                    $tax = ($item->tax_value) ? $item->value * $item->tax_value / 100 : 0;
                    return NumberFormatter::format($item->value + $tax);
                },
                'export_footer_total' => true,
            ]
        );

        return $columns;
    }

    public function getQuery(): Builder
    {
        return PurchaseOrderProductDetailRepository::datatable(
            $this->search,
            $this->dateStart,
            $this->dateEnd,
            productIds: collect($this->productIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
            categoryProductIds: collect($this->categoryProductIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
            supplierIds: collect($this->supplierIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray()
        );
    }

    /*
    | WITH DATATABLE EXPORT
    */
    function datatableExportFileName(): string
    {
        return 'Laporan Pembelian Barang Detail ' . Carbon::parse($this->dateStart)->format('Y-m-d') . ' sd ' . Carbon::parse($this->dateEnd)->format('Y-m-d');
    }

    function datatableExportFilter(): array
    {
        $productIds = collect($this->productIds)->map(function ($id) {
            return Crypt::decrypt($id);
        })->toArray();
        $productNames = ProductRepository::getBy(whereClause: [['id', $productIds]], orderByClause: [['name', 'ASC']])->pluck('name')->implode(', ');

        $categoryProductIds = collect($this->categoryProductIds)->map(function ($id) {
            return Crypt::decrypt($id);
        })->toArray();
        $categoryProductNames = CategoryProductRepository::getBy(whereClause: [['id', $categoryProductIds]], orderByClause: [['name', 'ASC']])->pluck('name')->implode(', ');

        $supplierIds = collect($this->supplierIds)->map(function ($id) {
            return Crypt::decrypt($id);
        })->toArray();
        $supplierNames = SupplierRepository::getBy(whereClause: [['id', $supplierIds]], orderByClause: [['name', 'ASC']])->pluck('name')->implode(', ');

        return [
            'Tanggal Mulai' => $this->dateStart,
            'Tanggal Akhir' => $this->dateEnd,
            'Produk' => $productNames,
            'Kategori Produk' => $categoryProductNames,
            'Supplier' => $supplierNames,
            'Kata Kunci' => $this->search,
        ];
    }
}
