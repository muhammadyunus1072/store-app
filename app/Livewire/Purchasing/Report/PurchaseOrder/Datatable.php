<?php

namespace App\Livewire\Purchasing\Report\PurchaseOrder;

use App\Exports\LivewireDatatableExport;
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
                'render' => function ($item) {
                    return $item->supplier_name;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai',
                'render' => function ($item) {
                    return NumberFormatter::format($item->value);
                },

                // EXPORT ATTRIBUTE
                'export' => function ($item, $index, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($item->value) : $item->value;
                },
                'export_footer_type' => LivewireDatatableExport::FOOTER_TYPE_SUM,
                'export_footer_data' => function ($item) {
                    return $item->value;
                },
                'export_footer_format' => function ($footerValue, $exportType) {
                    return $exportType == LivewireDatatableExport::EXPORT_PDF ? NumberFormatter::format($footerValue) : $footerValue;
                },
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

    function datatableExportTitle(): string
    {
        return 'Laporan Pembelian';
    }

    function datatableExportSubtitle(): array
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
}
