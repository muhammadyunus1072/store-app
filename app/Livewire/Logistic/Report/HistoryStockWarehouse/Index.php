<?php

namespace App\Livewire\Logistic\Report\HistoryStockWarehouse;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Report\HistoryStockWarehouse\HistoryStockWarehouseRepository;
use Illuminate\Support\Facades\Crypt;

class Index extends Component
{
    // Data
    public $histories = [];
    public $startQuantity;
    public $startValue;
    public $productName;
    public $productUnitName;

    // Filter
    public $dateStart;
    public $dateEnd;
    public $productId;
    public $warehouseId;

    public function render()
    {
        return view('livewire.logistic.report.history-stock-warehouse.index');
    }

    public function getData()
    {
        $this->histories = [];
        $this->productName = "";
        $this->productUnitName = "";

        if (empty($this->productId)) {
            return;
        }

        if (empty($this->warehouseId)) {
            return;
        }

        $productId = Crypt::decrypt($this->productId);
        $warehouseId = Crypt::decrypt($this->warehouseId);

        // PRODUCT INFO
        $product = ProductRepository::find($productId);
        $this->productName = $product->name;
        $this->productUnitName = $product->unit->unitDetailMain->name;

        // START INFO
        $startInfo = HistoryStockWarehouseRepository::getStartInfo($productId, Carbon::parse($this->dateStart)->subDay()->format("Y-m-d"), $warehouseId);
        $this->startQuantity = $startInfo ? $startInfo->quantity : 0;
        $this->startValue = $startInfo ? $startInfo->value : 0;

        // HISTORIES
        $this->histories = HistoryStockWarehouseRepository::getHistories($productId, $this->dateStart, $this->dateEnd, $warehouseId);
    }

    #[On('datatable-add-filter')]
    public function addFilter($filter)
    {
        foreach ($filter as $key => $value) {
            $this->$key = $value;
        }

        $this->getData();
    }
}
