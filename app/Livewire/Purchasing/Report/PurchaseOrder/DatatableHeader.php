<?php

namespace App\Livewire\Purchasing\Report\PurchaseOrder;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Livewire\WithDatatableHeader;
use App\Repositories\Purchasing\Report\PurchaseOrder\PurchaseOrderRepository;

class DatatableHeader extends Component
{
    use WithDatatableHeader;
    
    public $search;
    public $dateStart;
    public $dateEnd;
    public $supplierIds = [];
    public $productIds = [];
    public $categoryProductIds = [];

    public function mount()
    {
        $this->dateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function getHeaderData()
    {
        $data = PurchaseOrderRepository::datatable($this->search, $this->dateStart, $this->dateEnd, $this->supplierIds)->get();
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
