<?php

namespace App\Livewire\Logistic\Report\All\StockExpense;

use Livewire\Component;
use App\Traits\Livewire\WithDatatableHeader;
use App\Repositories\Logistic\Report\All\StockExpense\StockExpenseRepository;
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
        $data = StockExpenseRepository::datatable(
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

        $total = $data->sum('converted_quantity');
        return [
            [
                "col" => 3,
                "name" => "Total Jumlah Pengeluaran",
                "value" => $total
            ],
        ];
    }
}
