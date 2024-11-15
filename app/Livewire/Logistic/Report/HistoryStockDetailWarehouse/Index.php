<?php

namespace App\Livewire\Logistic\Report\HistoryStockDetailWarehouse;

use App\Repositories\Logistic\Master\Product\ProductRepository;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Repositories\Logistic\Report\HistoryStockDetailWarehouse\HistoryStockDetailWarehouseRepository;
use App\Settings\SettingLogistic;

class Index extends Component
{
    // Data
    public $data;
    public $productName;
    public $productUnitName;

    // Filter
    public $dateStart;
    public $dateEnd;
    public $productId;
    public $warehouseId;

    // Helper
    public $infoProductCode;
    public $infoProductExpiredDate;
    public $infoProductBatch;
    public $infoProductAttachment;

    public function render()
    {
        return view('livewire.logistic.report.history-stock-detail-warehouse.index');
    }

    public function mount()
    {
        $this->loadSetting();

        $this->dateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function loadSetting()
    {
        $this->infoProductCode = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_CODE);
        $this->infoProductExpiredDate = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_EXPIRED_DATE);
        $this->infoProductBatch = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_BATCH);
        $this->infoProductAttachment = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_ATTACHMENT);
    }

    public function getData()
    {
        $this->data = [];
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
        $startData = HistoryStockDetailWarehouseRepository::getStartInfo($this->productId, Carbon::parse($this->dateStart)->subDay()->format("Y-m-d"), $this->warehouseId);

        // HISTORIES
        $histories = HistoryStockDetailWarehouseRepository::getHistories($this->productId, $this->dateStart, $this->dateEnd, $this->warehouseId);

        // GROUPING HISTORIES
        // Init Data
        foreach ($startData as $item) {
            $key = $this->concatKey($item);
            if (!isset($this->data[$key])) {
                $this->data[$key] = [
                    'start_quantity' => $item->quantity,
                    'start_value' => $item->value,
                    'price' => $item->price,
                    'code' => $item->code,
                    'batch' => $item->batch,
                    'expired_date' => $item->expired_date,
                    'histories' => [],
                ];
            }
        }

        // Add History
        foreach ($histories as $item) {
            $key = $this->concatKey($item);
            if (!isset($this->data[$key])) {
                $this->data[$key] = [
                    'start_quantity' => 0,
                    'start_value' => 0,
                    'price' => $item->price,
                    'code' => $item->code,
                    'batch' => $item->batch,
                    'expired_date' => $item->expired_date,
                    'histories' => [],
                ];
            }
            $this->data[$key]['histories'][] = $item;
        }

        // Remove Last Stock = 0 with Empty Histories 
        foreach ($this->data as $key => $item) {
            if ($item['start_quantity'] == 0 && count($item['histories']) == 0) {
                unset($this->data[$key]);
            }
        }
    }

    public function concatKey($item)
    {
        return  "{$item->product_id}#{$item->price}#{$item->entry_date}#{$item->code}#{$item->batch}#{$item->expired_date}";
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
    public function export($type) {}
}
