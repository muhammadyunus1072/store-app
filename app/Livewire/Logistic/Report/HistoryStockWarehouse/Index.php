<?php

namespace App\Livewire\Logistic\Report\HistoryStockWarehouse;

use App\Helpers\General\ExportHelper;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;
use App\Repositories\Logistic\Report\HistoryStockWarehouse\HistoryStockWarehouseRepository;
use Illuminate\Support\Facades\Crypt;

class Index extends Component
{
    // Data
    public $histories = [];
    public $startQuantity;
    public $startValue;
    public $warehouseName;
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

        // WAREHOPUSE INFO
        $warehouse = WarehouseRepository::find($warehouseId);
        $this->warehouseName = $warehouse->name;

        // PRODUCT INFO
        $product = ProductRepository::find($productId);
        $this->productName = $product->name;
        $this->productUnitName = $product->unit->unitDetailMain->name;

        // START INFO
        $startInfo = HistoryStockWarehouseRepository::getStartInfo($productId, Carbon::parse($this->dateStart)->subDay()->format("Y-m-d"), $warehouseId);
        $this->startQuantity = $startInfo ? $startInfo->quantity : 0;
        $this->startValue = $startInfo ? $startInfo->value : 0;

        // HISTORIES
        $histories = HistoryStockWarehouseRepository::getHistories($productId, $this->dateStart, $this->dateEnd, $warehouseId);
        foreach ($histories as $index => $history) {
            $histories[$index]->remarksUrlButton = $history->remarksUrlButton();
        }
        $this->histories = $histories->toArray();
    }

    #[On('datatable-add-filter')]
    public function addFilter($filter)
    {
        foreach ($filter as $key => $value) {
            $this->$key = $value;
        }

        $this->getData();
    }

    public function export($type)
    {
        return ExportHelper::export(
            type: $type,
            fileName: "Kartu Stok - {$this->productName} - {$this->dateStart} sd {$this->dateEnd}",
            view: 'app.logistic.report.history-stock.export',
            data: [
                'histories' => $this->histories,
                'startQuantity' => $this->startQuantity,
                'startValue' => $this->startValue,
                'filters' => [
                    'Gudang' => $this->warehouseName,
                    'Produk' => $this->productName,
                    'Satuan' => $this->productUnitName,
                    'Tanggal Mulai' => $this->dateStart,
                    'Tanggal Akhir' => $this->dateEnd,
                ]
            ]
        );
    }
}
