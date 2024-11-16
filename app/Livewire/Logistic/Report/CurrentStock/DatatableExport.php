<?php

namespace App\Livewire\Logistic\Report\CurrentStock;

use Carbon\Carbon;
use Livewire\Component;
use App\Helpers\General\ExportHelper;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Livewire\WithDatatableExport;
use App\Repositories\Purchasing\Master\Supplier\SupplierRepository;

class DatatableExport extends Component
{
    use WithDatatableExport;

    public function setExport($data, $type, $filters = null)
    {
        $columns = [
            'name' => 'No',
            'render' => function($item, $index)
            {
                return $index + 1;
            }
        ];

        return [
            'file_name' => 'Data Pembelian ' . Carbon::parse($filters['date_start'])->format('Y-m-d') . ' sd ' . Carbon::parse($filters['date_end'])->format('Y-m-d'),
            'title' => 'Data Pembelian',
            'columns' => $columns
        ];
    }

    public function render()
    {
        return view('livewire.livewire-datatable-export');
    }
}
