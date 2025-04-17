<?php

namespace App\Livewire\Logistic\Report\Warehouse\CurrentStock;

use App\Exports\LivewireDatatableExport;
use Carbon\Carbon;
use Livewire\Component;
use App\Traits\Livewire\WithDatatable;
use App\Traits\Livewire\WithDatatableExport;
use App\Helpers\Core\UserStateHandler;
use App\Helpers\General\NumberFormatter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;
use App\Repositories\Logistic\Report\Warehouse\CurrentStock\CurrentStockRepository;

class Datatable extends Component
{
    use WithDatatable, WithDatatableExport;

    public $warehouseId;
    public $companyId;
    public $dateStart;
    public $dateEnd;
    public $productIds = [];
    public $categoryProductIds = [];

    public function onMount()
    {
        $this->loadUserState();
    }

    public function loadUserState()
    {
        $userState = UserStateHandler::get();
        $this->companyId = $userState['company_id'];
        $this->warehouseId = $userState['warehouse_id'];
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
    public function getColumns(): array
    {
        return [
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
                'key' => 'name',
                'name' => 'Nama Produk',
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Satuan',
                'render' => function ($item) {
                    return $item->unit_detail_name;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Stok Awal',
                'render' => function ($item) {
                    return NumberFormatter::format($item->stock_quantity - $item->quantity_stock_expense - $item->quantity_purchase_order - $item->quantity_stock_request_in - $item->quantity_stock_request_out);
                },

                // EXPORT ATTRIBUTE
                'export' => function ($item, $index, $exportType) {
                    $val = $item->stock_quantity - $item->quantity_stock_expense - $item->quantity_purchase_order - $item->quantity_stock_request_in - $item->quantity_stock_request_out;
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($val) : $val;
                },
                'export_footer_type' => LivewireDatatableExport::FOOTER_TYPE_SUM,
                'export_footer_data' => function ($item) {
                    return $item->stock_quantity - $item->quantity_stock_expense - $item->quantity_purchase_order - $item->quantity_stock_request_in - $item->quantity_stock_request_out;
                },
                'export_footer_format' => function ($footerValue, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($footerValue) : $footerValue;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Pembelian',
                'render' => function ($item) {
                    return NumberFormatter::format($item->quantity_purchase_order);
                },

                // EXPORT ATTRIBUTE
                'export' => function ($item, $index, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($item->quantity_purchase_order) : $item->quantity_purchase_order;
                },
                'export_footer_type' => LivewireDatatableExport::FOOTER_TYPE_SUM,
                'export_footer_data' => function ($item) {
                    return $item->quantity_purchase_order;
                },
                'export_footer_format' => function ($footerValue, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($footerValue) : $footerValue;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Transfer Masuk',
                'render' => function ($item) {
                    return NumberFormatter::format($item->quantity_stock_request_in);
                },

                // EXPORT ATTRIBUTE
                'export' => function ($item, $index, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($item->quantity_stock_request_in) : $item->quantity_stock_request_in;
                },
                'export_footer_type' => LivewireDatatableExport::FOOTER_TYPE_SUM,
                'export_footer_data' => function ($item) {
                    return $item->quantity_stock_request_in;
                },
                'export_footer_format' => function ($footerValue, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($footerValue) : $footerValue;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Transfer Keluar',
                'render' => function ($item) {
                    return NumberFormatter::format($item->quantity_stock_request_out * -1);
                },

                // EXPORT ATTRIBUTE
                'export' => function ($item, $index, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($item->quantity_stock_request_out * -1) : $item->quantity_stock_request_out * -1;
                },
                'export_footer_type' => LivewireDatatableExport::FOOTER_TYPE_SUM,
                'export_footer_data' => function ($item) {
                    return $item->quantity_stock_request_out * -1;
                },
                'export_footer_format' => function ($footerValue, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($footerValue) : $footerValue;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Pengeluaran',
                'render' => function ($item) {
                    return NumberFormatter::format($item->quantity_stock_expense * -1);
                },

                // EXPORT ATTRIBUTE
                'export' => function ($item, $index, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($item->quantity_stock_expense * -1) : $item->quantity_stock_expense * -1;
                },
                'export_footer_type' => LivewireDatatableExport::FOOTER_TYPE_SUM,
                'export_footer_data' => function ($item) {
                    return $item->quantity_stock_expense * -1;
                },
                'export_footer_format' => function ($footerValue, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($footerValue) : $footerValue;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Stok Akhir',
                'render' => function ($item) {
                    return NumberFormatter::format($item->stock_quantity);
                },

                // EXPORT ATTRIBUTE
                'export' => function ($item, $index, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($item->stock_quantity) : $item->stock_quantity;
                },
                'export_footer_type' => LivewireDatatableExport::FOOTER_TYPE_SUM,
                'export_footer_data' => function ($item) {
                    return $item->stock_quantity;
                },
                'export_footer_format' => function ($footerValue, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($footerValue) : $footerValue;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Awal',
                'render' => function ($item) {
                    return NumberFormatter::format($item->stock_value - $item->value_stock_expense - $item->value_purchase_order - $item->value_stock_request_in - $item->value_stock_request_out);
                },

                // EXPORT ATTRIBUTE
                'export' => function ($item, $index, $exportType) {
                    $val = $item->stock_value - $item->value_stock_expense - $item->value_purchase_order - $item->value_stock_request_in - $item->value_stock_request_out;
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($val) : $val;
                },
                'export_footer_type' => LivewireDatatableExport::FOOTER_TYPE_SUM,
                'export_footer_data' => function ($item) {
                    return $item->stock_value - $item->value_stock_expense - $item->value_purchase_order - $item->value_stock_request_in - $item->value_stock_request_out;
                },
                'export_footer_format' => function ($footerValue, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($footerValue) : $footerValue;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Pembelian',
                'render' => function ($item) {
                    return NumberFormatter::format($item->value_purchase_order);
                },

                // EXPORT ATTRIBUTE
                'export' => function ($item, $index, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($item->value_purchase_order) : $item->value_purchase_order;
                },
                'export_footer_type' => LivewireDatatableExport::FOOTER_TYPE_SUM,
                'export_footer_data' => function ($item) {
                    return $item->value_purchase_order;
                },
                'export_footer_format' => function ($footerValue, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($footerValue) : $footerValue;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Transfer Masuk',
                'render' => function ($item) {
                    return NumberFormatter::format($item->value_stock_request_in);
                },

                // EXPORT ATTRIBUTE
                'export' => function ($item, $index, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($item->value_stock_request_in) : $item->value_stock_request_in;
                },
                'export_footer_type' => LivewireDatatableExport::FOOTER_TYPE_SUM,
                'export_footer_data' => function ($item) {
                    return $item->value_stock_request_in;
                },
                'export_footer_format' => function ($footerValue, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($footerValue) : $footerValue;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Transfer Keluar',
                'render' => function ($item) {
                    return NumberFormatter::format($item->value_stock_request_out * -1);
                },

                // EXPORT ATTRIBUTE
                'export' => function ($item, $index, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($item->value_stock_request_out * -1) : $item->value_stock_request_out * -1;
                },
                'export_footer_type' => LivewireDatatableExport::FOOTER_TYPE_SUM,
                'export_footer_data' => function ($item) {
                    return $item->value_stock_request_out * -1;
                },
                'export_footer_format' => function ($footerValue, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($footerValue) : $footerValue;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Pengeluaran',
                'render' => function ($item) {
                    return NumberFormatter::format($item->value_stock_expense * -1);
                },

                // EXPORT ATTRIBUTE
                'export' => function ($item, $index, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($item->value_stock_expense * -1) : $item->value_stock_expense * -1;
                },
                'export_footer_type' => LivewireDatatableExport::FOOTER_TYPE_SUM,
                'export_footer_data' => function ($item) {
                    return $item->value_stock_expense * -1;
                },
                'export_footer_format' => function ($footerValue, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($footerValue) : $footerValue;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Akhir',
                'render' => function ($item) {
                    return NumberFormatter::format($item->stock_value);
                },

                // EXPORT ATTRIBUTE
                'export' => function ($item, $index, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($item->stock_value) : $item->stock_value;
                },
                'export_footer_type' => LivewireDatatableExport::FOOTER_TYPE_SUM,
                'export_footer_data' => function ($item) {
                    return $item->stock_value;
                },
                'export_footer_format' => function ($footerValue, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($footerValue) : $footerValue;
                },
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return CurrentStockRepository::datatable(
            $this->search,
            $this->dateStart,
            $this->dateEnd,
            productIds: collect($this->productIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
            categoryProductIds: collect($this->categoryProductIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
            locationId: $this->warehouseId ? Crypt::decrypt($this->warehouseId) : null,
        );
    }

    public function getView(): string
    {
        return 'livewire.logistic.report.warehouse.current-stock.datatable';
    }

    /*
    | WITH DATATABLE EXPORT
    */
    function datatableExportFileName(): string
    {
        return 'Laporan Stok Gudang ' . Carbon::parse($this->dateStart)->format('Y-m-d') . ' sd ' . Carbon::parse($this->dateEnd)->format('Y-m-d');
    }

    function datatableExportTitle(): string
    {
        return 'Laporan Stok Gudang';
    }

    function datatableExportSubtitle(): array
    {
        $productIds = collect($this->productIds)->map(function ($id) {
            return Crypt::decrypt($id);
        })->toArray();
        $productNames = ProductRepository::getBy(whereClause: [['id', $productIds]], orderByClause: [['name', 'ASC']])->pluck('name')->implode(', ');

        $categoryProductIds = collect($this->categoryProductIds)->map(function ($id) {
            return Crypt::decrypt($id);
        })->toArray();
        $categoryProductNames = CategoryProductRepository::getBy(whereClause: [['id', $categoryProductIds]], orderByClause: [['name', 'ASC']])->pluck('name')->implode(', ');

        $warehouseName = $this->warehouseId ? WarehouseRepository::find(Crypt::decrypt($this->warehouseId))->name : null;

        return [
            'Tanggal Mulai' => $this->dateStart,
            'Tanggal Akhir' => $this->dateEnd,
            'Produk' => $productNames,
            'Kategori Produk' => $categoryProductNames,
            'Gudang' => $warehouseName,
            'Kata Kunci' => $this->search,
        ];
    }
}
