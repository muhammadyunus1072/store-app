<?php

namespace App\Livewire\Purchasing\Report\PurchaseOrder;

use Carbon\Carbon;
use Livewire\Component;
use App\Helpers\General\ExportHelper;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use App\Repositories\Logistic\Report\Expense\ExpenseRepository;
use App\Repositories\Purchasing\Master\Supplier\SupplierRepository;
use App\Repositories\Purchasing\Report\PurchaseOrder\PurchaseOrderRepository;

class Datatable extends Component
{
    use WithDatatable;

    public $date_start;
    public $date_end;
    public $supplier_id;

    public $header = [];
    public $show_header = true;

    public function onMount()
    {
        $this->date_start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->date_end = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    #[On('export')]
    public function export($type)
    {
        // dd("OWE");
        // $data = $this->datatableGetProcessedQuery()->get(); 
        // $columns = [
        //     'name' => 'No',
        //     'render' => function($item, $index)
        //     {
        //         dd('OKE');
        //         // return $index + 1;
        //     }
        // ];
        $this->dispatch('consoleLog', 'WIO');
        // $this->dispatch('datatable-export-handler'
        //     // $columns,
        //     // $type, 
        //     // [
        //     //     'date_start' => $this->date_start,
        //     //     'date_end' => $this->date_end,
        //     //     'supplier' => $this->supplier_id ? SupplierRepository::find(Crypt::decrypt($this->supplier_id))->name : null,
        //     //     'keyword' => $this->search,
        //     // ]
        // );
    }
    
    public function getColumns(): array
    {
        return [
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'No',
                'render' => function($item, $index)
                {
                    return $index + 1;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Tanggal',
                'render' => function($item)
                {
                    return Carbon::parse($item->transaction_date)->translatedFormat('d F Y');
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nomor',
                'render' => function($item)
                {
                    return $item->number;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Supplier',
                'render' => function($item)
                {
                    return $item->supplier_name;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->value);
                }
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return PurchaseOrderRepository::datatable($this->search, $this->date_start, $this->date_end, $this->supplier_id ? Crypt::decrypt($this->supplier_id) : null);
    }

    public function getView(): string
    {
        $this->dispatch('datatable-header-handler', $this->datatableGetProcessedQuery()->get());
        return 'livewire.purchasing.report.purchase-order.datatable';
    }
}
