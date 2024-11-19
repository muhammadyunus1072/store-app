<?php

namespace App\Livewire\Logistic\Report\HistoryStock;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Report\HistoryStock\HistoryStockRepository;
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
        return view('livewire.logistic.report.history-stock.index');
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
        $this->startQuantity = $startInfo ? $startInfo->quantity : 0;
        $this->startValue = $startInfo ? $startInfo->value : 0;

        // HISTORIES
        $this->histories = HistoryStockRepository::getHistories($productId, $this->dateStart, $this->dateEnd);
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
