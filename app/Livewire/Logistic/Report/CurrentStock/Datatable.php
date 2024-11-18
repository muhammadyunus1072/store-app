<?php

namespace App\Livewire\Logistic\Report\CurrentStock;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Report\CurrentStock\CurrentStockRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;
use App\Traits\Livewire\WithDatatableExport;

class Datatable extends Component
{
    use WithDatatable, WithDatatableExport;

    public $dateStart;
    public $dateEnd;
    public $productIds = [];
    public $categoryProductIds = [];

    public function onMount()
    {
        $this->dateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
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

    function datatableExportFileName(): string
    {
        return 'Laporan Stok Akhir ' . Carbon::parse($this->dateStart)->format('Y-m-d') . ' sd ' . Carbon::parse($this->dateEnd)->format('Y-m-d');
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
            'Kata Kunci' => $this->search,
        ];
    }

    function datatableExportEnableFooterTotal()
    {
        return [2, 3, 4, 5, 6, 7, 8, 9, 10];
    }

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
                'footer' => 'Total',
                'name' => 'Satuan',
                'render' => function ($item) {
                    return $item->unit_detail_name;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Stok Awal',
                'render' => function ($item) {
                    return NumberFormatter::format($item->stock_quantity - $item->quantity_stock_expense - $item->quantity_purchase_order);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Pembelian',
                'render' => function ($item) {
                    return NumberFormatter::format($item->quantity_purchase_order);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Pengeluaran',
                'render' => function ($item) {
                    return NumberFormatter::format($item->quantity_stock_expense * -1);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Stok Akhir',
                'render' => function ($item) {
                    return NumberFormatter::format($item->stock_quantity);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Awal',
                'render' => function ($item) {
                    return NumberFormatter::format($item->stock_value - $item->value_stock_expense - $item->value_purchase_order);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Pembelian',
                'render' => function ($item) {
                    return NumberFormatter::format($item->value_purchase_order);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Pengeluaran',
                'render' => function ($item) {
                    return NumberFormatter::format($item->value_stock_expense * -1);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Akhir',
                'render' => function ($item) {
                    return NumberFormatter::format($item->stock_value);
                }
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return CurrentStockRepository::datatable($this->search, $this->dateStart, $this->dateEnd, $this->productIds, $this->categoryProductIds);
    }

    public function getView(): string
    {
        return 'livewire.logistic.report.current-stock.datatable';
    }
}
