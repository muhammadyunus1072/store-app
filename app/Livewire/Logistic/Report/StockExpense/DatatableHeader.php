<?php

namespace App\Livewire\Logistic\Report\StockExpense;

use App\Traits\Livewire\WithDatatableHeader;
use Livewire\Component;

class DatatableHeader extends Component
{
    use WithDatatableHeader;
    public $header = [];
    
    private function getHeader($data)
    {
        $data = collect($data);
        
        $total = $data->sum('converted_quantity');
        $this->header = [
            [
                "col" => 3,
                "name" => "Total Jumlah Pengeluaran",
                "value" => $total
            ],
        ];
    }

    public function render()
    {
        return view('livewire.logistic.report.stock-expense.datatable-header');
    }
}
