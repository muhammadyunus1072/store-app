<?php

namespace App\Livewire\Logistic\Report\Warehouse\StockRequestIn;

use Livewire\Component;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\Core\UserStateHandler;
use App\Traits\Livewire\WithDatatableHeader;
use App\Repositories\Logistic\Report\Warehouse\StockRequestIn\StockRequestInRepository;

class DatatableHeader extends Component
{
    use WithDatatableHeader;

    public $search;
    public $warehouseId;
    public $warehouseIds = [];
    public $companyId;
    public $dateStart;
    public $dateEnd;
    public $productIds = [];
    public $categoryProductIds = [];

    public function mount()
    {
        $this->loadUserState();
    }

    public function loadUserState()
    {
        $userState = UserStateHandler::get();
        $this->companyId = $userState['company_id'];
        $this->warehouseId = $userState['warehouse_id'];
    }

    public function getHeaderData()
    {
        $data = StockRequestInRepository::datatable(
            $this->search,
            $this->dateStart,
            $this->dateEnd,
            productIds: collect($this->productIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
            categoryProductIds: collect($this->categoryProductIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
            warehouseId: $this->warehouseId ? Crypt::decrypt($this->warehouseId) : null,
            warehouseIds: collect($this->warehouseIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
        )->get();

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
