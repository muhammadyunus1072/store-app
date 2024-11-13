<?php

namespace App\Livewire\Logistic\Report\HistoryStockDetail;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Settings\SettingLogistic;
use App\Helpers\General\ExportHelper;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Core\User\UserRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Report\HistoryStock\HistoryStockRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;
use App\Repositories\Logistic\Report\HistoryStockDetail\HistoryStockDetailRepository;

class Datatable extends Component
{
    use WithDatatable;

    public $date_start;
    public $date_end;
    public $products = [];
    public $category_products = [];

    public $isInputProductCode;
    public $isInputProductExpiredDate;
    public $isInputProductBatch;

    public function onMount()
    {
        $this->date_start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->date_end = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->loadSetting();
    }

    public function loadSetting()
    {
        $this->isInputProductCode = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_CODE);
        $this->isInputProductExpiredDate = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_EXPIRED_DATE);
        $this->isInputProductBatch = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_BATCH);
    }

    #[On('export')]
    public function export($type)
    {
        $fileName = 'Data Kartu Stok Detail ' . Carbon::parse($this->date_start)->format('Y-m-d') . ' sd ' . Carbon::parse($this->date_end)->format('Y-m-d');
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
        $data = $this->datatableGetProcessedQuery()->get();

        $products = collect($this->products)->map(function ($id) {
            return ProductRepository::find($id)->name;
        });
        $category_products = collect($this->category_products)->map(function ($id) {
            return CategoryProductRepository::find($id)->name;
        });
        return ExportHelper::export(
            $type,
            $fileName,
            $data,
            "app.logistic.report.history-stock-detail.export",
            [
                'date_start' => $this->date_start,
                'date_end' => $this->date_end,
                'products' => $products,
                'category_products' => $category_products,
                'colspan' => $colspan,
                'isInputProductCode' => $this->isInputProductCode,
                'isInputProductExpiredDate' => $this->isInputProductExpiredDate,
                'isInputProductBatch' => $this->isInputProductBatch,
                'keyword' => $this->search,
                'type' => $type,
                'title' => 'Data Kartu Stok Detail',
            ],
            [
                'size' => 'legal',
                'orientation' => 'portrait',
            ]
        );
    }

    public function getColumns(): array
    {
        $columns = [
            [
                'key' => 'transaction_date',
                'name' => 'Tanggal',
            ],
            [
                'key' => 'product_name',
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
                'key' => 'unit_detail_name',
                'name' => 'Satuan',
            ],
            [
                'key' => 'start_stock',
                'name' => 'Stok Awal',
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah',
                'render' => function ($item) {
                    return NumberFormatter::format(abs($item->quantity));
                }
            ],
            [
                'key' => 'last_stock',
                'name' => 'Stok Akhir',
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Awal',
                'render' => function ($item) {
                    return NumberFormatter::format($item->start_stock * $item->price);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai',
                'render' => function ($item) {
                    return NumberFormatter::format(abs($item->quantity * $item->price));
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Akhir',
                'render' => function ($item) {
                    return NumberFormatter::format($item->last_stock * $item->price);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Keterangan',
                'render' => function ($item) {
                    $authUser = UserRepository::authenticatedUser();
                    $url = route($item->remarksTable->remarksTableInfo()['route_name'], Crypt::encrypt($item->remarksMasterTable->id));
                    $button = $authUser->hasPermissionTo($item->remarksTable->remarksTableInfo()['access_name']) ?
                        " <a class='btn btn-primary btn-sm' href='$url' target='_BLANK'>
                                <i class='ki-duotone ki-notepad-edit fs-1'>
                                    <span class='path1'></span>
                                    <span class='path2'></span>
                                </i>
                                Lihat
                            </a>" :
                        NULL;

                    return $item->remarksTable->remarksTableInfo()['translated_name'] . " " . $item->remarksMasterTable->number . " " . $button;
                }
            ],
        );

        return $columns;
    }

    public function getQuery(): Builder
    {
        return HistoryStockDetailRepository::datatable($this->search, $this->date_start, $this->date_end, $this->products, $this->category_products);
    }

    public function getView(): string
    {
        return 'livewire.logistic.report.history-stock-detail.datatable';
    }
}
