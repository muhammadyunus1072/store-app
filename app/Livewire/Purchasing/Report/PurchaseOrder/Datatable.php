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
        $fileName = 'Data Pembelian ' . Carbon::parse($this->date_start)->format('Y-m-d') . ' sd ' . Carbon::parse($this->date_end)->format('Y-m-d');

        $data = $this->datatableGetProcessedQuery()->get();

        return ExportHelper::export(
            $type,
            $fileName,
            $data,
            "app.purchasing.report.purchase-order.export",
            [
                'date_start' => $this->date_start,
                'date_end' => $this->date_end,
                'supplier' => $this->supplier_id ? SupplierRepository::find(Crypt::decrypt($this->supplier_id))->name : null,
                'keyword' => $this->search,
                'type' => $type,
                'title' => 'Data Pembelian',
            ],
            [
                'size' => 'legal',
                'orientation' => 'portrait',
            ]
        );
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

    private function setHeader()
    {
        $data = $this->datatableGetProcessedQuery()->get();
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

    public function getView(): string
    {
        $this->setHeader();
        return 'livewire.purchasing.report.purchase-order.datatable';
    }
}
