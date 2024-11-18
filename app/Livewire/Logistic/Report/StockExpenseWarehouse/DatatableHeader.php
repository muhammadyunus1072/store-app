<?php

namespace App\Livewire\Logistic\Report\StockExpenseWarehouse;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\Core\UserStateHandler;
use App\Traits\Livewire\WithDatatableHeader;
use App\Repositories\Logistic\Report\StockExpenseWarehouse\StockExpenseWarehouseRepository;

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
        $data = StockExpenseWarehouseRepository::datatable($this->search, $this->dateStart, $this->dateEnd, $this->productIds, $this->categoryProductIds, $this->warehouseId)->get();
        $total = $data->sum('converted_quantity');
        return [
            [
                "col" => 3,
                "name" => "Total Jumlah Pengeluaran",
                "value" => $total
            ],
        ];
    }
}
