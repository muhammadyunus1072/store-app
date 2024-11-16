<?php

namespace App\Livewire\Logistic\Report\CurrentStock;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Helpers\General\ExportHelper;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Livewire\WithDatatableHeader;
use Laravel\SerializableClosure\SerializableClosure;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Report\CurrentStock\CurrentStockRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;

class Datatable extends Component
{
    use WithDatatable;

    public $date_start;
    public $date_end;
    public $products = [];
    public $category_products = [];

    public function onMount()
    {
        $this->date_start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->date_end = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    #[On('export')]
    public function export($type)
    {
        $data = $this->datatableGetProcessedQuery()->get(); 
        $columns = [
            [
                'name' => 'No',
                'render' => function ($item, $index) {
                    return $index + 1;
                }
            ],
            [
                'name' => 'Nama Produk',
                'render' => function ($item)
                {
                    return $item['name'];
                }
            ],
            [
                'name' => 'Satuan',
                'withFooter' => true,
                'footer' => 'Total',
                'footerColspan' => 3,
                'render' => function ($item)
                {
                    return $item['unit_detail_name'];
                }
            ],
            [
                'name' => 'Stok Awal',
                'withFooter' => true,
                'render' => function ($item)
                {
                    return $item['last_stock'] - $item['expense_quantity'] - $item['purchase_quantity'];
                }
            ],
            [
                'name' => 'Jumlah Pembelian',
                'withFooter' => true,
                'render' => function ($item)
                {
                    return $item['purchase_quantity'];
                }
            ],
            [
                'name' => 'Jumlah Pengeluaran',
                'withFooter' => true,
                'render' => function ($item)
                {
                    return $item['expense_quantity'] * -1;
                }
            ],
            [
                'name' => 'Stok Akhir',
                'withFooter' => true,
                'render' => function ($item)
                {
                    return $item['last_stock'];
                }
            ],
            [
                'name' => 'Nilai Awal',
                'withFooter' => true,
                'render' => function ($item)
                {
                    return $item['last_stock_value'] - $item['expense_value'] - $item['purchase_value'];
                }
            ],
            [
                'name' => 'Nilai Pembelian',
                'withFooter' => true,
                'render' => function ($item)
                {
                    return $item['purchase_value'];
                }
            ],
            [
                'name' => 'Nilai Pengeluaran',
                'withFooter' => true,
                'render' => function ($item)
                {
                    return $item['expense_value'] * -1;
                }
            ],
            [
                'name' => 'Nilai Akhir',
                'withFooter' => true,
                'render' => function ($item)
                {
                    return $item['last_stock_value'];
                }
            ],
        ];

        $products = collect($this->products)->map(function ($id) {
            return ProductRepository::find($id)->name;
        })->toArray();
        $category_products = collect($this->category_products)->map(function ($id) {
            return CategoryProductRepository::find($id)->name;
        })->toArray();

        $columns = serialize(collect($columns)->map(function ($column) {
            if (isset($column['render']) && is_callable($column['render'])) {
                $column['render'] = new SerializableClosure($column['render']);
            }
            return $column;
        })->toArray());
        $this->dispatch('datatable-export-handler', 
            $data,
            $columns,
            $type, 
            'Data Stok Akhir', 
            [
                'Tanggal Mulai' => $this->date_start,
                'Tanggal Akhir' => $this->date_end,
                'Produk' => implode(" , ", $products),
                'Kategori Produk' => implode(" , ", $category_products),
                'Kata Kunci' => $this->search,
            ],
            'Data Stok Akhir ' . Carbon::parse($this->date_start)->format('Y-m-d') . ' sd ' . Carbon::parse($this->date_end)->format('Y-m-d')
        );
    }

    public function getColumns(): array
    {
        return [
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
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Stok Awal',
                'render' => function ($item) {
                    return NumberFormatter::format($item->last_stock - $item->expense_quantity - $item->purchase_quantity);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Pembelian',
                'render' => function ($item) {
                    return NumberFormatter::format($item->purchase_quantity);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Pengeluaran',
                'render' => function ($item) {
                    return NumberFormatter::format($item->expense_quantity * -1);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Stok Akhir',
                'render' => function ($item) {
                    return NumberFormatter::format($item->last_stock);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Awal',
                'render' => function ($item) {
                    return NumberFormatter::format($item->last_stock_value - $item->expense_value - $item->purchase_value);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Pembelian',
                'render' => function ($item) {
                    return NumberFormatter::format($item->purchase_value);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Pengeluaran',
                'render' => function ($item) {
                    return NumberFormatter::format($item->expense_value * -1);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Akhir',
                'render' => function ($item) {
                    return NumberFormatter::format($item->last_stock_value);
                }
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return CurrentStockRepository::datatable($this->search, $this->date_start, $this->date_end, $this->products, $this->category_products);
    }

    public function getView(): string
    {
        $this->dispatch('datatable-header-handler', $this->datatableGetProcessedQuery()->get());
        return 'livewire.logistic.report.current-stock.datatable';
    }
}
