<?php

namespace App\Livewire\Logistic\Report\All\HistoryStock;

use App\Helpers\General\ExportHelper;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Report\All\HistoryStock\HistoryStockRepository;
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

    public function render()
    {
        return view('livewire.logistic.report.all.history-stock.index');
    }

    public function getData()
    {
        $this->histories = [];
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
        $startInfo = HistoryStockRepository::getStartInfo($productId, Carbon::parse($this->dateStart)->subDay()->format("Y-m-d"));
        $this->startQuantity = $startInfo->quantity ? $startInfo->quantity : 0;
        $this->startValue = $startInfo->value ? $startInfo->value : 0;

        // HISTORIES
        $histories = HistoryStockRepository::getHistories($productId, $this->dateStart, $this->dateEnd);
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
            view: 'app.logistic.report.all.history-stock.export',
            data: [
                'histories' => $this->histories,
                'startQuantity' => $this->startQuantity,
                'startValue' => $this->startValue,
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
