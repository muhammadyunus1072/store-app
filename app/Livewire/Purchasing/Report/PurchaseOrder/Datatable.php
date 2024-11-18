<?php

namespace App\Livewire\Purchasing\Report\PurchaseOrder;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Helpers\General\ExportHelper;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Livewire\WithDatatableExport;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Report\Expense\ExpenseRepository;
use App\Repositories\Purchasing\Master\Supplier\SupplierRepository;
use App\Repositories\Purchasing\Report\PurchaseOrder\PurchaseOrderRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;

class Datatable extends Component
{
    use WithDatatable, WithDatatableExport;

    public $dateStart;
    public $dateEnd;
    public $supplierIds = [];

    public $header = [];
    public $show_header = true;

    public function onMount()
    {
        $this->dateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedSearch()
    {
        $this->dispatch('add-filter', [
            'search' => $this->search,
        ]);
    }

    #[On('add-filter')]
    public function addFilter($filter)
    {
        foreach ($filter as $key => $value) {
            $this->$key = $value;
        }        
    }
    
    function datatableExportFileName(): string
    {
        return 'Laporan Pembelian ' . Carbon::parse($this->dateStart)->format('Y-m-d') . ' sd ' . Carbon::parse($this->dateEnd)->format('Y-m-d');
    }

    function datatableExportFilter(): array
    {
        $supplierIds = collect($this->supplierIds)->map(function ($id) {
            return SupplierRepository::find($id)->name;
        })->toArray();
        return [
            'Tanggal Mulai' => $this->dateStart,
            'Tanggal Akhir' => $this->dateEnd,
            'Supplier' => implode(" , ", $supplierIds),
            'Kata Kunci' => $this->search,
        ];
    }

    function datatableExportEnableFooterTotal()
    {
        return [3, 4];
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
                'footer' => 'Total',
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
        return PurchaseOrderRepository::datatable($this->search, $this->dateStart, $this->dateEnd, $this->supplierIds);
    }

    public function getView(): string
    {
        return 'livewire.purchasing.report.purchase-order.datatable';
    }
}
