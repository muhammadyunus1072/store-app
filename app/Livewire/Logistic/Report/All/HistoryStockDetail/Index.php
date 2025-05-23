<?php

namespace App\Livewire\Logistic\Report\All\HistoryStockDetail;

use App\Helpers\General\ExportHelper;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Repositories\Logistic\Report\All\HistoryStockDetail\HistoryStockDetailRepository;
use App\Settings\SettingLogistic;
use Illuminate\Support\Facades\Crypt;

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

    // Helper
    public $infoProductCode;
    public $infoProductExpiredDate;
    public $infoProductBatch;
    public $infoProductAttachment;

    public function render()
    {
        return view('livewire.logistic.report.all.history-stock-detail.index');
    }

    public function mount()
    {
        $this->loadSetting();
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

        $productId = Crypt::decrypt($this->productId);

        // PRODUCT INFO
        $product = ProductRepository::find($productId);
        $this->productName = $product->name;
        $this->productUnitName = $product->unit->unitDetailMain->name;

        // START INFO
        $startData = HistoryStockDetailRepository::getStartInfo($productId, Carbon::parse($this->dateStart)->subDay()->format("Y-m-d"));

        // HISTORIES
        $histories = HistoryStockDetailRepository::getHistories($productId, $this->dateStart, $this->dateEnd);

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
            $item->remarksUrlButton = $item->remarksUrlButton();
            $this->data[$key]['histories'][] = $item->toArray();
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
            fileName: "Kartu Stok Detail - {$this->productName} - {$this->dateStart} sd {$this->dateEnd}",
            view: 'app.logistic.report.all.history-stock-detail.export',
            data: [
                'data' => $this->data,
                'infoProductCode' => $this->infoProductCode,
                'infoProductExpiredDate' => $this->infoProductExpiredDate,
                'infoProductBatch' => $this->infoProductBatch,
                'infoProductAttachment' => $this->infoProductAttachment,
                'filters' => [
                    'Produk' => $this->productName,
                    'Satuan' => $this->productUnitName,
                    'Tanggal Mulai' => $this->dateStart,
                    'Tanggal Akhir' => $this->dateEnd,
                ]
            ]
        );
    }
}
