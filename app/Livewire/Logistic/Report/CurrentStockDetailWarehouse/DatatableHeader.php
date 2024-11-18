<?php

namespace App\Livewire\Logistic\Report\CurrentStockDetailWarehouse;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\Core\UserStateHandler;
use App\Traits\Livewire\WithDatatableHeader;
use App\Repositories\Logistic\Report\CurrentStockDetailWarehouse\CurrentStockDetailWarehouseRepository;

class DatatableHeader extends Component
{
    use WithDatatableHeader;
    
    public $search;
    public $dateStart;
    public $dateEnd;
    public $productIds = [];
    public $categoryProductIds = [];

    // Helpers
    public $isMultipleCompany = false;

    public $companies = [];
    public $warehouses = [];

    public $warehouseId;
    public $warehouseText;

    public $companyId;
    public $companyText;

    public function mount()
    {
        $this->dateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
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
            $this->warehouseId = Crypt::decrypt($userState['warehouse_id']);
        }
    }

    public function getHeaderData()
    {
        $data = CurrentStockDetailWarehouseRepository::datatable($this->search, $this->dateStart, $this->dateEnd, $this->productIds, $this->categoryProductIds, $this->warehouseId)->get();
        $stock_quantity = $data->sum('stock_quantity');
        $quantity_purchase_order = $data->sum('quantity_purchase_order');
        $quantity_stock_expense = $data->sum('quantity_stock_expense');
        $first_stock = $stock_quantity - $quantity_purchase_order - $quantity_stock_expense;
        $incoming_tranfer_quantity = $data->sum('incoming_tranfer_quantity');
        $outgoing_tranfer_quantity = $data->sum('outgoing_tranfer_quantity');
        $stock_value = $data->sum('stock_value');
        $value_purchase_order = $data->sum('value_purchase_order');
        $value_stock_expense = $data->sum('value_stock_expense');
        $incoming_tranfer_value = $data->sum('incoming_tranfer_value');
        $outgoing_tranfer_value = $data->sum('outgoing_tranfer_value');
        $first_stock_value = $stock_value - $value_purchase_order - $value_stock_expense;
        return [
            // ROW 1
            [
                "col" => 2,
                "name" => "Total Stok Awal",
                "value" => $first_stock
            ],
            [
                "col" => 2,
                "name" => "Total Jumlah Pembelian",
                "value" => $quantity_purchase_order
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
                "value" => $quantity_stock_expense * -1
            ],
            [
                "col" => 2,
                "name" => "Total Stok Akhir",
                "value" => $stock_quantity
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
                "value" => $value_purchase_order
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
                "value" => $value_stock_expense * -1
            ],
            [
                "col" => 2,
                "name" => "Total Nilai Akhir",
                "value" => $stock_value
            ],
        ];
    }
}
