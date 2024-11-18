<?php

namespace App\Livewire\Logistic\Report\StockExpense;

use Carbon\Carbon;
use Livewire\Component;
use App\Traits\Livewire\WithDatatableHeader;
use App\Repositories\Logistic\Report\StockExpense\StockExpenseRepository;

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
        $data = StockExpenseRepository::datatable($this->search, $this->dateStart, $this->dateEnd, $this->productIds, $this->categoryProductIds)->get();
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
