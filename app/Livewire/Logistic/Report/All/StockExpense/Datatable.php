<?php

namespace App\Livewire\Logistic\Report\All\StockExpense;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Livewire\WithDatatableExport;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Report\All\StockExpense\StockExpenseRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;

class Datatable extends Component
{
    use WithDatatable, WithDatatableExport;

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
                'name' => 'Gudang',
                'render' => function ($item) {
                    return $item->warehouse_name;
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
                'footer' => 'Total',
                'render' => function ($item) {
                    return $item->unit_detail_name;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Konversi',
                'render' => function ($item) {
                    return NumberFormatter::format($item->converted_quantity);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Satuan Konversi',
                'footer' => '',
                'render' => function ($item) {
                    return $item->main_unit_detail_name;
                }
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return StockExpenseRepository::datatable(
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
        return 'livewire.logistic.report.all.stock-expense.datatable';
    }

    /*
    | WITH DATATABLE EXPORT
    */
    function datatableExportFileName(): string
    {
        return 'Laporan Pengeluaran ' . Carbon::parse($this->dateStart)->format('Y-m-d') . ' sd ' . Carbon::parse($this->dateEnd)->format('Y-m-d');
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

    function datatableExportEnableFooterTotal()
    {
        return [6, 7, 8];
    }
}
