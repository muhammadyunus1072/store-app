<?php

namespace App\Livewire\Logistic\Report\CurrentStockWarehouse;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Helpers\General\ExportHelper;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\Core\UserStateHandler;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Report\CurrentStock\CurrentStockRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;
use App\Repositories\Logistic\Report\CurrentStockWarehouse\CurrentStockWarehouseRepository;

class Datatable extends Component
{
    use WithDatatable;

    public $date_start;
    public $date_end;
    public $products = [];
    public $category_products = [];

    public $header = [];
    public $show_header = true;

    // Helpers
    public $isMultipleCompany = false;

    public $companies = [];
    public $warehouses = [];

    public $warehouseId;
    public $warehouseText;

    public $companyId;
    public $companyText;

    public function onMount()
    {
        $this->date_start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->date_end = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->loadUserState();
    }

    public function loadUserState()
    {
        $userState = UserStateHandler::get();
        if ($this->isMultipleCompany) {
            $this->companies = $userState['companies'];
            $this->companyId = $userState['company_id'];
            $this->warehouses = $userState['warehouses'];
            $this->warehouseId = $userState['warehouse_id'];
        } else {
            $this->companyId = $userState['company_id'];
            $this->warehouses = $userState['warehouses'];
            $this->warehouseId = $userState['warehouse_id'];
        }
    }
    
    #[On('export')]
    public function export($type)
    {
        $fileName = 'Data Stok Akhir Gudang ' . Carbon::parse($this->date_start)->format('Y-m-d') . ' sd ' . Carbon::parse($this->date_end)->format('Y-m-d');

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
            "app.logistic.report.current-stock-warehouse.export",
            [
                'date_start' => $this->date_start,
                'date_end' => $this->date_end,
                'products' => $products,
                'category_products' => $category_products,
                'warehouse' => $this->warehouseId ? WarehouseRepository::find(Crypt::decrypt($this->warehouseId))->name : null,
                'keyword' => $this->search,
                'type' => $type,
                'title' => 'Data Stok Akhir Gudang',
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
                'key' => 'name',
                'name' => 'Nama Produk',
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
                'name' => 'Stok Awal',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->last_stock - $item->expense_quantity - $item->purchase_quantity);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Pembelian',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->purchase_quantity);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Tranfer Masuk',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->incoming_tranfer_quantity);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Tranfer Keluar',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->outgoing_tranfer_quantity * -1);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah Pengeluaran',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->expense_quantity * -1);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Stok Akhir',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->last_stock);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Awal',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->last_stock_value - $item->expense_value - $item->purchase_value);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Pembelian',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->purchase_value);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Tranfer Masuk',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->incoming_tranfer_value);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Tranfer Keluar',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->outgoing_tranfer_value * -1);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Pengeluaran',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->expense_value * -1);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Akhir',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->last_stock_value);
                }
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return CurrentStockWarehouseRepository::datatable($this->search, $this->date_start, $this->date_end, $this->products, $this->category_products, $this->warehouseId ? Crypt::decrypt($this->warehouseId) : null);
    }

    private function setHeader()
    {
        $data = $this->datatableGetProcessedQuery()->get();
        $last_stock = $data->sum('last_stock');
        $purchase_quantity = $data->sum('purchase_quantity');
        $expense_quantity = $data->sum('expense_quantity');
        $first_stock = $last_stock - $purchase_quantity - $expense_quantity;
        $incoming_tranfer_quantity = $data->sum('incoming_tranfer_quantity');
        $outgoing_tranfer_quantity = $data->sum('outgoing_tranfer_quantity');
        $last_stock_value = $data->sum('last_stock_value');
        $purchase_value = $data->sum('purchase_value');
        $expense_value = $data->sum('expense_value');
        $incoming_tranfer_value = $data->sum('incoming_tranfer_value');
        $outgoing_tranfer_value = $data->sum('outgoing_tranfer_value');
        $first_stock_value = $last_stock_value - $purchase_value - $expense_value;
        $this->header = [
            // ROW 1
            [
                "col" => 2,
                "name" => "Total Stok Awal",
                "value" => $first_stock
            ],
            [
                "col" => 2,
                "name" => "Total Jumlah Pembelian",
                "value" => $purchase_quantity
            ],
            [
                "col" => 2,
                "name" => "Total Jumlah Tranfer Masuk",
                "value" => $incoming_tranfer_quantity
            ],
            [
                "col" => 2,
                "name" => "Total Jumlah Tranfer Keluar",
                "value" => $outgoing_tranfer_quantity * -1
            ],
            [
                "col" => 2,
                "name" => "Total Jumlah Pengeluaran",
                "value" => $expense_quantity * -1
            ],
            [
                "col" => 2,
                "name" => "Total Stok Akhir",
                "value" => $last_stock
            ],

            // ROW 2
            [
                "col" => 2,
                "name" => "Total Nilai Awal",
                "value" => $first_stock_value
            ],
            [
                "col" => 2,
                "name" => "Total Nilai Pembelian",
                "value" => $purchase_value
            ],
            [
                "col" => 2,
                "name" => "Total Nilai Tranfer Masuk",
                "value" => $incoming_tranfer_value
            ],
            [
                "col" => 2,
                "name" => "Total Nilai Tranfer Keluar",
                "value" => $outgoing_tranfer_value * -1
            ],
            [
                "col" => 2,
                "name" => "Total Nilai Pengeluaran",
                "value" => $expense_value * -1
            ],
            [
                "col" => 2,
                "name" => "Total Nilai Akhir",
                "value" => $last_stock_value
            ],
        ];
    }

    public function getView(): string
    {
        $this->setHeader();
        return 'livewire.logistic.report.current-stock-warehouse.datatable';
    }
}
