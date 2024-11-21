<?php

namespace App\Livewire\Logistic\Report\All\StockExpired;

use Livewire\Component;
use App\Traits\Livewire\WithDatatableHeader;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Logistic\Report\All\StockExpired\StockExpiredRepository;

class DatatableHeader extends Component
{
    use WithDatatableHeader;

    public $search;
    public $expiredDateStart;
    public $expiredDateEnd;
    public $productIds = [];
    public $categoryProductIds = [];

    public function getHeaderData()
    {
        $data = StockExpiredRepository::datatable(
            $this->search,
            $this->expiredDateStart,
            $this->expiredDateEnd,
            productIds: collect($this->productIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
            categoryProductIds: collect($this->categoryProductIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
        )->get();

        $stock_quantity = $data->sum('stock_qty');
        $stock_value = $data->sum('stock_value');
        return [
            [
                "col" => 3,
                "name" => "Jumlah Stok",
                "value" => $stock_quantity
            ],
            [
                "col" => 3,
                "name" => "Total Nilai Stok",
                "value" => $stock_value
            ],
        ];
    }
}
