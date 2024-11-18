<?php

namespace App\Livewire\Logistic\Report\CurrentStockDetail;

use Carbon\Carbon;
use Livewire\Component;
use App\Traits\Livewire\WithDatatableHeader;
use App\Repositories\Logistic\Report\CurrentStockDetail\CurrentStockDetailRepository;

class DatatableHeader extends Component
{
    use WithDatatableHeader;
    
    public $search;
    public $dateStart;
    public $dateEnd;
    public $productIds = [];
    public $categoryProductIds = [];

    public function mount()
    {
        $this->dateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function getHeaderData()
    {
        $data = CurrentStockDetailRepository::datatable($this->search, $this->dateStart, $this->dateEnd, $this->productIds, $this->categoryProductIds)->get();
        $stock_quantity = $data->sum('stock_quantity');
        $quantity_purchase_order = $data->sum('quantity_purchase_order');
        $quantity_stock_expense = $data->sum('quantity_stock_expense');
        $first_stock = $stock_quantity - $quantity_purchase_order - $quantity_stock_expense;
        $stock_value = $data->sum('stock_value');
        $value_purchase_order = $data->sum('value_purchase_order');
        $value_stock_expense = $data->sum('value_stock_expense');
        $first_stock_value = $stock_value - $value_purchase_order - $value_stock_expense;
        return [
            [
                "col" => 3,
                "name" => "Total Stok Awal",
                "value" => $first_stock
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
                "value" => $first_stock_value
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
