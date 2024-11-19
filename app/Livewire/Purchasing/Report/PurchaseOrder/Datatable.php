<?php

namespace App\Livewire\Purchasing\Report\PurchaseOrder;

use Carbon\Carbon;
use Livewire\Component;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Livewire\WithDatatableExport;
use App\Repositories\Purchasing\Master\Supplier\SupplierRepository;
use App\Repositories\Purchasing\Report\PurchaseOrder\PurchaseOrderRepository;
use Illuminate\Support\Facades\Crypt;

class Datatable extends Component
{
    use WithDatatable, WithDatatableExport;

    // Filter
    public $dateStart;
    public $dateEnd;
    public $supplierIds = [];

    public function updatedSearch()
    {
        $this->dispatch('on-search-updated', [
            'search' => $this->search,
        ]);
    }

    /*
    | WITH DATATABLE
    */
    public function getView(): string
    {
        return 'livewire.purchasing.report.purchase-order.datatable';
    }

    public function getColumns(): array
    {
        return [
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'No',
                'render' => function ($item, $index) {
                    return $index + 1;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Tanggal',
                'render' => function ($item) {
                    return Carbon::parse($item->transaction_date)->translatedFormat('d F Y');
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nomor',
                'render' => function ($item) {
                    return $item->number;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Supplier',
                'footer' => 'Total',
                'render' => function ($item) {
                    return $item->supplier_name;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai',
                'render' => function ($item) {
                    return NumberFormatter::format($item->value);
                }
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return PurchaseOrderRepository::datatable(
            search: $this->search,
            dateStart: $this->dateStart,
            dateEnd: $this->dateEnd,
            supplierIds: collect($this->supplierIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray()
        );
    }

    /*
    | WITH DATATABLE EXPORT
    */

    function datatableExportFileName(): string
    {
        return 'Laporan Pembelian ' . Carbon::parse($this->dateStart)->format('Y-m-d') . ' sd ' . Carbon::parse($this->dateEnd)->format('Y-m-d');
    }

    function datatableExportFilter(): array
    {
        $supplierIds = collect($this->supplierIds)->map(function ($id) {
            return Crypt::decrypt($id);
        })->toArray();
        $supplierNames = SupplierRepository::getBy(whereClause: [['id', $supplierIds]], orderByClause: [['name', 'ASC']])->pluck('name')->implode(', ');

        return [
            'Tanggal Mulai' => $this->dateStart,
            'Tanggal Akhir' => $this->dateEnd,
            'Supplier' => $supplierNames,
            'Kata Kunci' => $this->search,
        ];
    }

    function datatableExportEnableFooterTotal()
    {
        return [3, 4];
    }
}
