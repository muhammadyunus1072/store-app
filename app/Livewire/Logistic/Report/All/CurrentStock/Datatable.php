<?php

namespace App\Livewire\Logistic\Report\All\CurrentStock;

use App\Helpers\General\ExportHelper;
use Carbon\Carbon;
use Livewire\Component;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Report\All\CurrentStock\CurrentStockRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;
use App\Traits\Livewire\WithDatatableExport;
use Illuminate\Support\Facades\Crypt;

class Datatable extends Component
{
    use WithDatatable, WithDatatableExport;

    // Filter
    public $dateStart;
    public $dateEnd;
    public $productIds = [];
    public $categoryProductIds = [];

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
                },
                'export' => false,
                'export_footer_total' => false,
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'key' => 'name',
                'name' => 'Nama Produk',
                'export' => function($item, $index, $exportType){
                    return $exportType;
                },
                'export_footer_total' => 'Total'
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Satuan',
                'render' => function ($item) {
                    return $item->unit_detail_name;
                },
                'export' => true,
                'export_footer_total' => false,
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Stok Awal',
                'render' => function ($item) {
                    return NumberFormatter::format($item->stock_quantity - $item->quantity_stock_expense - $item->quantity_purchase_order);
                },
                'export_footer_total' => function($value, $index, $exportType) {
                    return $exportType == ExportHelper::TYPE_PDF ? NumberFormatter::format($value) : $value;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Pembelian',
                'render' => function ($item) {
                    return NumberFormatter::format($item->quantity_purchase_order);
                },
                'export_footer_total' => true,
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Pengeluaran',
                'render' => function ($item) {
                    return NumberFormatter::format($item->quantity_stock_expense * -1);
                },
                'export_footer_total' => true,
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Stok Akhir',
                'render' => function ($item) {
                    return NumberFormatter::format($item->stock_quantity);
                },
                'export_footer_total' => true,
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Awal',
                'render' => function ($item) {
                    return NumberFormatter::format($item->stock_value - $item->value_stock_expense - $item->value_purchase_order);
                },
                'export_footer_total' => true,
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Pembelian',
                'render' => function ($item) {
                    return NumberFormatter::format($item->value_purchase_order);
                },
                'export_footer_total' => true,
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Pengeluaran',
                'render' => function ($item) {
                    return NumberFormatter::format($item->value_stock_expense * -1);
                },
                'export_footer_total' => true,
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Akhir',
                'render' => function ($item) {
                    return NumberFormatter::format($item->stock_value);
                },
                'export_footer_total' => true,
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
        );
    }

    public function getView(): string
    {
        return 'livewire.logistic.report.all.current-stock.datatable';
    }

    /*
    | WITH DATATABLE EXPORT
    */
    function datatableExportFileName(): string
    {
        return 'Laporan Stok Akhir ' . Carbon::parse($this->dateStart)->format('Y-m-d') . ' sd ' . Carbon::parse($this->dateEnd)->format('Y-m-d');
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

        return [
            'Tanggal Mulai' => $this->dateStart,
            'Tanggal Akhir' => $this->dateEnd,
            'Produk' => $productNames,
            'Kategori Produk' => $categoryProductNames,
            'Kata Kunci' => $this->search,
        ];
    }
}
