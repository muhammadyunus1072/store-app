<?php

namespace App\Livewire\Purchasing\Report\PurchaseOrder;

use App\Traits\Livewire\WithDatatableHeader;
use Livewire\Component;

class DatatableHeader extends Component
{
    use WithDatatableHeader;
    public $header = [];
    
    private function getHeader($data)
    {
        $data = collect($data);
        $total_qty = $data->count();
        $total_value = $data->sum('value');
        $this->header = [
            [
                "col" => 3,
                "name" => "Jumlah Transaksi",
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
        return view('livewire.purchasing.report.purchase-order.datatable-header');
    }
}
