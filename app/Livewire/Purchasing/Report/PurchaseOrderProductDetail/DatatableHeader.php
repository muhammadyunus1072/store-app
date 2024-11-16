<?php

namespace App\Livewire\Purchasing\Report\PurchaseOrderProductDetail;

use App\Traits\Livewire\WithDatatableHeader;
use Livewire\Component;

class DatatableHeader extends Component
{
    use WithDatatableHeader;
    public $header = [];
    
    private function getHeader($data)
    {
        $data = collect($data);
        $total_purchase_order = collect($data)->unique('purchase_order_id')->count();
        $total_qty = $data->sum('converted_quantity');
        $total_value = $data->sum('value');
        $this->header = [
            [
                "col" => 3,
                "name" => "Jumlah Transaksi",
                "value" => $total_purchase_order
            ],
            [
                "col" => 3,
                "name" => "Jumlah Quantity",
                "value" => $total_qty
            ],
            [
                "col" => 3,
                "name" => "Total Nilai",
                "value" => $total_value
            ],
        ];
    }

    public function render()
    {
        return view('livewire.purchasing.report.purchase-order-product-detail.datatable-header');
    }
}
