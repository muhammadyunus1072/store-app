<?php

namespace App\Livewire\Purchasing\Report\PurchaseOrder;

use Livewire\Component;
use App\Traits\Livewire\WithDatatableHeader;
use App\Repositories\Purchasing\Report\PurchaseOrder\PurchaseOrderRepository;
use Illuminate\Support\Facades\Crypt;

class DatatableHeader extends Component
{
    use WithDatatableHeader;

    public $search;
    public $dateStart;
    public $dateEnd;
    public $supplierIds = [];
    public $productIds = [];
    public $categoryProductIds = [];

    public function getHeaderData()
    {
        $data = PurchaseOrderRepository::datatable(
            search: $this->search,
            dateStart: $this->dateStart,
            dateEnd: $this->dateEnd,
            supplierIds: collect($this->supplierIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray()
        )->get();

        $total_qty = $data->count();
        $total_value = $data->sum('value');
        return [
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
}
