<?php

namespace App\Livewire\Purchasing\Report\PurchaseOrderProductDetail;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Settings\SettingLogistic;
use App\Helpers\General\ExportHelper;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Report\Expense\ExpenseRepository;
use App\Repositories\Purchasing\Master\Supplier\SupplierRepository;
use App\Repositories\Purchasing\Report\PurchaseOrder\PurchaseOrderRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;
use App\Repositories\Purchasing\Report\PurchaseOrderProduct\PurchaseOrderProductRepository;
use App\Repositories\Purchasing\Report\PurchaseOrderProductDetail\PurchaseOrderProductDetailRepository;
use App\Settings\SettingPurchasing;

class Datatable extends Component
{
    use WithDatatable;

    public $date_start;
    public $date_end;
    public $products = [];
    public $category_products = [];
    public $supplier_id;

    public $header = [];
    public $show_header = true;

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
        $this->isInputProductCode = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_PRODUCT_CODE);
        $this->isInputProductExpiredDate = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_PRODUCT_EXPIRED_DATE);
        $this->isInputProductBatch = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_PRODUCT_BATCH);
    }

    #[On('export')]
    public function export($type)
    {
        $fileName = 'Data Pembelian Barang Detail ' . Carbon::parse($this->date_start)->format('Y-m-d') . ' sd ' . Carbon::parse($this->date_end)->format('Y-m-d');
        $colspan = 0;
        if($this->isInputProductCode)
        {
            $colspan ++;
        }
        if($this->isInputProductExpiredDate)
        {
            $colspan ++;
        }
        if($this->isInputProductBatch)
        {
            $colspan ++;
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
            "app.purchasing.report.purchase-order-product-detail.export",
            [
                'date_start' => $this->date_start,
                'date_end' => $this->date_end,
                'products' => $products,
                'category_products' => $category_products,
                'supplier' => $this->supplier_id ? SupplierRepository::find(Crypt::decrypt($this->supplier_id))->name : null,
                'keyword' => $this->search,
                'isInputProductCode' => $this->isInputProductCode,
                'isInputProductExpiredDate' => $this->isInputProductExpiredDate,
                'isInputProductBatch' => $this->isInputProductBatch,
                'colspan' => $colspan,
                'type' => $type,
                'title' => 'Data Pembelian Barang Detail',
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
                'name' => 'Produk',
                'render' => function($item)
                {
                    return $item->product_name;
                }
            ],
        ];

        if($this->isInputProductCode)
        {
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
        if($this->isInputProductBatch)
        {
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
        if($this->isInputProductExpiredDate)
        {
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
                'name' => 'Jumlah',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->quantity);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Satuan',
                'render' => function($item)
                {
                    return $item->unit_detail_name;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Harga Satuan',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->price);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Konversi',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->converted_quantity);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Satuan Konversi',
                'render' => function($item)
                {
                    return $item->main_unit_detail_name;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Total',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->value);
                }
            ]
        );

        return $columns;
    }

    public function getQuery(): Builder
    {
        return PurchaseOrderProductDetailRepository::datatable($this->search, $this->date_start, $this->date_end, $this->products, $this->category_products, $this->supplier_id ? Crypt::decrypt($this->supplier_id) : null);
    }

    private function setHeader()
    {
        $data = $this->datatableGetProcessedQuery()->get();
        $total_purchase_order = collect($data)->unique('purchase_order_id')->count();
        $total_qty = $data->sum('converted_quantity');
        $total_value = $data->sum('value');
        $this->header = [
            [
                "col" => 3,
                "name" => "Jumlah Transaksi",
                "value" => $total_purchase_order
            ],
            [
                "col" => 3,
                "name" => "Jumlah Quantity",
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
        return 'livewire.purchasing.report.purchase-order-product-detail.datatable';
    }
}
