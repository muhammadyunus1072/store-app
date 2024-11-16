<?php

namespace App\Livewire\Logistic\Report\CurrentStockDetailWarehouse;

use App\Traits\Livewire\WithDatatableHeader;
use Livewire\Component;

class DatatableHeader extends Component
{
    use WithDatatableHeader;
    public $header = [];
    
    private function getHeader($data)
    {
        $data = collect($data);
        $last_stock = $data->sum('last_stock');
        $purchase_quantity = $data->sum('purchase_quantity');
        $expense_quantity = $data->sum('expense_quantity');
        $first_stock = $last_stock - $purchase_quantity - $expense_quantity;
        $incoming_tranfer_quantity = $data->sum('incoming_tranfer_quantity');
        $outgoing_tranfer_quantity = $data->sum('outgoing_tranfer_quantity');
        $last_stock_value = $data->sum('last_stock_value');
        $purchase_value = $data->sum('purchase_value');
        $expense_value = $data->sum('expense_value');
        $incoming_tranfer_value = $data->sum('incoming_tranfer_value');
        $outgoing_tranfer_value = $data->sum('outgoing_tranfer_value');
        $first_stock_value = $last_stock_value - $purchase_value - $expense_value;
        $this->header = [
            // ROW 1
            [
                "col" => 2,
                "name" => "Total Stok Awal",
                "value" => $first_stock
            ],
            [
                "col" => 2,
                "name" => "Total Jumlah Pembelian",
                "value" => $purchase_quantity
            ],
            [
                "col" => 2,
                "name" => "Total Jumlah Tranfer Masuk",
                "value" => $incoming_tranfer_quantity
            ],
            [
                "col" => 2,
                "name" => "Total Jumlah Tranfer Keluar",
                "value" => $outgoing_tranfer_quantity * -1
            ],
            [
                "col" => 2,
                "name" => "Total Jumlah Pengeluaran",
                "value" => $expense_quantity * -1
            ],
            [
                "col" => 2,
                "name" => "Total Stok Akhir",
                "value" => $last_stock
            ],

            // ROW 2
            [
                "col" => 2,
                "name" => "Total Nilai Awal",
                "value" => $first_stock_value
            ],
            [
                "col" => 2,
                "name" => "Total Nilai Pembelian",
                "value" => $purchase_value
            ],
            [
                "col" => 2,
                "name" => "Total Nilai Tranfer Masuk",
                "value" => $incoming_tranfer_value
            ],
            [
                "col" => 2,
                "name" => "Total Nilai Tranfer Keluar",
                "value" => $outgoing_tranfer_value * -1
            ],
            [
                "col" => 2,
                "name" => "Total Nilai Pengeluaran",
                "value" => $expense_value * -1
            ],
            [
                "col" => 2,
                "name" => "Total Nilai Akhir",
                "value" => $last_stock_value
            ],
        ];
    }

    public function render()
    {
        return view('livewire.logistic.report.current-stock-detail-warehouse.datatable-header');
    }
}
