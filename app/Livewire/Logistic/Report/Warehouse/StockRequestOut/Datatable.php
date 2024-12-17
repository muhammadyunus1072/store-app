<?php

namespace App\Livewire\Logistic\Report\Warehouse\StockRequestOut;

use App\Exports\LivewireDatatableExport;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\Core\UserStateHandler;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Livewire\WithDatatableExport;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;
use App\Repositories\Logistic\Report\Warehouse\StockRequestOut\StockRequestOutRepository;

class Datatable extends Component
{
    use WithDatatable, WithDatatableExport;

    public $warehouseId;
    public $warehouseIds = [];
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
                'name' => 'Gudang Asal',
                'render' => function ($item) {
                    return $item->source_warehouse_name;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Gudang Penerima',
                'render' => function ($item) {
                    return $item->destination_warehouse_name;
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
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Konversi',
                'render' => function ($item) {
                    return NumberFormatter::format($item->converted_quantity);
                },

                // EXPORT ATTRIBUTE
                'export' => function ($item, $index, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($item->converted_quantity) : $item->converted_quantity;
                },
                'export_footer_type' => LivewireDatatableExport::FOOTER_TYPE_SUM,
                'export_footer_data' => function ($item) {
                    return $item->converted_quantity;
                },
                'export_footer_format' => function ($footerValue, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($footerValue) : $footerValue;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Satuan Konversi',
                'footer' => '',
                'render' => function ($item) {
                    return $item->main_unit_detail_name;
                },
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return StockRequestOutRepository::datatable(
            $this->search,
            $this->dateStart,
            $this->dateEnd,
            productIds: collect($this->productIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
            categoryProductIds: collect($this->categoryProductIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
            warehouseId: $this->warehouseId ? Crypt::decrypt($this->warehouseId) : null,
            warehouseIds: collect($this->warehouseIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
        );
    }

    public function getView(): string
    {
        return 'livewire.logistic.report.warehouse.stock-request-out.datatable';
    }

    /*
    | WITH DATATABLE EXPORT
    */
    function datatableExportFileName(): string
    {
        return 'Laporan Permintaan Keluar Gudang ' . Carbon::parse($this->dateStart)->format('Y-m-d') . ' sd ' . Carbon::parse($this->dateEnd)->format('Y-m-d');
    }

    function datatableExportTitle(): string
    {
        return 'Laporan Permintaan Keluar Gudang';
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

        $warehouseIds = collect($this->warehouseIds)->map(function ($id) {
            return Crypt::decrypt($id);
        })->toArray();
        $warehouseNames = WarehouseRepository::getBy(whereClause: [['id', $warehouseIds]], orderByClause: [['name', 'ASC']])->pluck('name')->implode(', ');

        return [
            'Tanggal Mulai' => $this->dateStart,
            'Tanggal Akhir' => $this->dateEnd,
            'Produk' => $productNames,
            'Kategori Produk' => $categoryProductNames,
            'Gudang Penerima' => $warehouseName,
            'Gudang Asal' => $warehouseNames,
            'Kata Kunci' => $this->search,
        ];
    }
}
