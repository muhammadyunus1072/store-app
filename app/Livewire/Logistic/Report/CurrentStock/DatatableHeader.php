<?php

namespace App\Livewire\Logistic\Report\CurrentStock;

use Carbon\Carbon;
use Livewire\Component;
use App\Traits\Livewire\WithDatatableHeader;
use App\Repositories\Logistic\Report\CurrentStock\CurrentStockRepository;
use Illuminate\Support\Facades\Crypt;

class DatatableHeader extends Component
{
    use WithDatatableHeader;

    public $search;
    public $dateStart;
    public $dateEnd;
    public $productIds = [];
    public $categoryProductIds = [];

    public function getHeaderData()
    {
        $data = CurrentStockRepository::datatable(
            $this->search,
            $this->dateStart,
            $this->dateEnd,
            productIds: collect($this->productIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
            categoryProductIds: collect($this->categoryProductIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
        )->get();

        $stock_quantity = $data->sum('stock_quantity');
        $quantity_purchase_order = $data->sum('quantity_purchase_order');
        $quantity_stock_expense = $data->sum('quantity_stock_expense');
        $start_stock = $stock_quantity - $quantity_purchase_order - $quantity_stock_expense;
        $stock_value = $data->sum('stock_value');
        $value_purchase_order = $data->sum('value_purchase_order');
        $value_stock_expense = $data->sum('value_stock_expense');
        $start_value = $stock_value - $value_purchase_order - $value_stock_expense;
        return [
            [
                "col" => 3,
                "name" => "Total Stok Awal",
                "value" => $start_stock
            ],
            [
                "col" => 3,
                "name" => "Total Jumlah Pembelian",
                "value" => $quantity_purchase_order
            ],
            [
                "col" => 3,
                "name" => "Total Jumlah Pengeluaran",
                "value" => $quantity_stock_expense * -1
            ],
            [
                "col" => 3,
                "name" => "Total Stok Akhir",
                "value" => $stock_quantity
            ],
            [
                "col" => 3,
                "name" => "Total Nilai Awal",
                "value" => $start_value
            ],
            [
                "col" => 3,
                "name" => "Total Nilai Pembelian",
                "value" => $value_purchase_order
            ],
            [
                "col" => 3,
                "name" => "Total Nilai Pengeluaran",
                "value" => $value_stock_expense * -1
            ],
            [
                "col" => 3,
                "name" => "Total Nilai Akhir",
                "value" => $stock_value
            ],
        ];
    }
}
