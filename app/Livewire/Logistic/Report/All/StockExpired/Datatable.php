<?php

namespace App\Livewire\Logistic\Report\All\StockExpired;

use Carbon\Carbon;
use Livewire\Component;
use App\Settings\SettingLogistic;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Livewire\WithDatatableExport;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;
use App\Repositories\Logistic\Report\All\StockExpired\StockExpiredRepository;
use Illuminate\Support\Facades\Crypt;

class Datatable extends Component
{
    use WithDatatable, WithDatatableExport;

    // Filter
    public $expiredDateStart;
    public $expiredDateEnd;
    public $productIds = [];
    public $categoryProductIds = [];

    // Setting
    public $isInputProductCode;
    public $isInputProductExpiredDate;
    public $isInputProductBatch;

    public function onMount()
    {
        $this->loadSetting();
    }

    public function loadSetting()
    {
        $this->isInputProductCode = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_CODE);
        $this->isInputProductExpiredDate = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_EXPIRED_DATE);
        $this->isInputProductBatch = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_BATCH);
    }

    public function updatedSearch()
    {
        $this->dispatch('on-search-updated', [
            'search' => $this->search,
        ]);
    }

    /*
    | WITH DATATABLE
    */
    public function getColumns(): array
    {
        $columns = [
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
                'key' => 'name',
                'name' => 'Nama Produk',
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Harga',
                'render' => function ($item) {
                    return NumberFormatter::format($item->price);
                }
            ],
        ];

        if ($this->isInputProductCode) {
            $columns[] =
                [
                    'sortable' => false,
                    'searchable' => false,
                    'name' => 'Kode',
                    'render' => function ($item) {
                        return $item->code;
                    }
                ];
        }
        if ($this->isInputProductBatch) {
            $columns[] =
                [
                    'sortable' => false,
                    'searchable' => false,
                    'name' => 'Batch',
                    'render' => function ($item) {
                        return $item->batch;
                    }
                ];
        }
        if ($this->isInputProductExpiredDate) {
            $columns[] =
                [
                    'sortable' => false,
                    'searchable' => false,
                    'name' => 'Expired Date',
                    'render' => function ($item) {
                        return $item->expired_date;
                    }
                ];
        }

        array_push(
            $columns,
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Satuan',
                'render' => function ($item) {
                    return $item->unit_detail_name;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Stok',
                'render' => function ($item) {
                    return NumberFormatter::format($item->stock_qty);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai',
                'render' => function ($item) {
                    return NumberFormatter::format($item->stock_value);
                }
            ],
        );
        return $columns;
    }

    public function getQuery(): Builder
    {
        return StockExpiredRepository::datatable(
            $this->search,
            $this->expiredDateStart,
            $this->expiredDateEnd,
            productIds: collect($this->productIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
            categoryProductIds: collect($this->categoryProductIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
        );
    }

    public function getView(): string
    {
        return 'livewire.logistic.report.all.stock-expired.datatable';
    }

    /*
    | WITH DATATABLE
    */
    function datatableExportFileName(): string
    {
        return 'Laporan Stok Expired ' . Carbon::parse($this->expiredDateStart)->format('Y-m-d') . ' sd ' . Carbon::parse($this->expiredDateEnd)->format('Y-m-d');
    }

    function datatableExportFilter(): array
    {
        $productIds = collect($this->productIds)->map(function ($id) {
            return Crypt::decrypt($id);
        })->toArray();
        $productNames = ProductRepository::getBy(whereClause: [['id', $productIds]], orderByClause: [['name', 'ASC']])->pluck('name')->implode(', ');

        $categoryProductIds = collect($this->categoryProductIds)->map(function ($id) {
            return Crypt::decrypt($id);
        })->toArray();
        $categoryProductNames = CategoryProductRepository::getBy(whereClause: [['id', $categoryProductIds]], orderByClause: [['name', 'ASC']])->pluck('name')->implode(', ');

        return [
            'Tanggal ED Mulai' => $this->expiredDateStart,
            'Tanggal ED Akhir' => $this->expiredDateEnd,
            'Produk' => $productNames,
            'Kategori Produk' => $categoryProductNames,
            'Kata Kunci' => $this->search,
        ];
    }

    function datatableExportEnableFooterTotal()
    {
        $colspan = 0;
        if ($this->isInputProductCode) {
            $colspan++;
        }
        if ($this->isInputProductExpiredDate) {
            $colspan++;
        }
        if ($this->isInputProductBatch) {
            $colspan++;
        }
        return [4 + $colspan, 5 + $colspan];
    }
}
