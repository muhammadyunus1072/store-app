<?php

namespace App\Livewire\Logistic\Report\HistoryStockWarehouse;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Report\HistoryStockWarehouse\HistoryStockWarehouseRepository;

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

    public function mount()
    {
        $this->dateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function getData()
    {
        $this->histories = [];
        $this->productName = "";
        $this->productUnitName = "";

        if (empty($this->productId)) {
            return;
        }

        // PRODUCT INFO
        $product = ProductRepository::find($this->productId);
        $this->productName = $product->name;
        $this->productUnitName = $product->unit->unitDetailMain->name;

        // START INFO
        $startInfo = HistoryStockWarehouseRepository::getStartInfo($this->productId, Carbon::parse($this->dateStart)->subDay()->format("Y-m-d"), $this->warehouseId);
        $this->startQuantity = $startInfo ? $startInfo->quantity : 0;
        $this->startValue = $startInfo ? $startInfo->value : 0;

        // HISTORIES
        $this->histories = HistoryStockWarehouseRepository::getHistories($this->productId, $this->dateStart, $this->dateEnd, $this->warehouseId);
    }

    #[On('add-filter')]
    public function addFilter($filter)
    {
        foreach ($filter as $key => $value) {
            $this->$key = $value;
        }

        $this->getData();
    }

    #[On('export')]
    public function export($type)
    {

    }
}
