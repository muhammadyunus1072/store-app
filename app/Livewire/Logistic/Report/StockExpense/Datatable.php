<?php

namespace App\Livewire\Logistic\Report\StockExpense;

use Carbon\Carbon;
use Livewire\Component;
use App\Helpers\General\ExportHelper;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use App\Repositories\Logistic\Report\StockExpense\StockExpenseRepository;

class Datatable extends Component
{
    use WithDatatable;

    public $date_start;
    public $date_end;
    public $products = [];
    public $category_products = [];

    public $header = [];
    public $show_header = true;

    public function onMount()
    {
        $this->date_start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->date_end = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    #[On('export')]
    public function export($type)
    {
        $fileName = 'Data Pengeluaran ' . Carbon::parse($this->date_start)->format('Y-m-d') . ' sd ' . Carbon::parse($this->date_end)->format('Y-m-d');

        $data = $this->datatableGetProcessedQuery()->get();

        $products = collect($this->products)->map(function ($id) {
            return ProductRepository::find($id)->name;
        });
        $category_products = collect($this->category_products)->map(function ($id) {
            return CategoryProductRepository::find($id)->name;
        });
        return ExportHelper::export(
            $type,
            $fileName,
            $data,
            "app.logistic.report.stock-expense.export",
            [
                'date_start' => $this->date_start,
                'date_end' => $this->date_end,
                'products' => $products,
                'category_products' => $category_products,
                'keyword' => $this->search,
                'type' => $type,
                'title' => 'Data Pengeluaran',
            ],
            [
                'size' => 'legal',
                'orientation' => 'portrait',
            ]
        );
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
                'name' => 'Gudang',
                'render' => function($item)
                {
                    return $item->warehouse_name;
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
                'render' => function($item)
                {
                    return $item->main_unit_detail_name;
                }
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return StockExpenseRepository::datatable($this->search, $this->date_start, $this->date_end, $this->products, $this->category_products);
    }

    private function setHeader()
    {
        $data = $this->datatableGetProcessedQuery()->get();
        $total = $data->sum('converted_quantity');
        $this->header = [
            [
                "col" => 3,
                "name" => "Total Jumlah Pengeluaran",
                "value" => $total
            ],
        ];
    }

    public function getView(): string
    {
        $this->setHeader();
        return 'livewire.logistic.report.stock-expense.datatable';
    }
}
