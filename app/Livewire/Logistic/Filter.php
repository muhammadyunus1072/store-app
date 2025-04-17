<?php

namespace App\Livewire\Logistic;

use Livewire\Component;
use App\Settings\SettingCore;
use App\Settings\SettingLogistic;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\Core\UserStateHandler;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use App\Models\Logistic\Master\DisplayRack\DisplayRack;
use App\Repositories\Logistic\Master\DisplayRack\DisplayRackRepository;

class Filter extends Component
{
    public $prefixRoute = 'logistic.filter.';
    public $dispatchEvent = 'datatable-add-filter';

    // Filter
    public $companyId;
    public $locationType;
    public $displayRackId;
    public $warehouseId;
    public $warehouseIds = [];
    public $dateStart;
    public $dateEnd;
    public $entryDateStart;
    public $entryDateEnd;
    public $expiredDateStart;
    public $expiredDateEnd;
    public $productId;
    public $productIds = [];
    public $categoryProductIds = [];

    // Setting Filter
    public $filterLocationType;
    public $filterDisplayRack;
    public $filterWarehouse;
    public $filterWarehouseMultiple;
    public $filterCompany;
    public $filterProduct;
    public $filterProductMultiple;
    public $filterCategoryProductMultiple;
    public $filterEntryDateStart;
    public $filterEntryDateEnd;
    public $filterExpiredDateStart;
    public $filterExpiredDateEnd;
    public $filterDateStart;
    public $filterDateEnd;

    // Setting
    public $infoProductCode;
    public $infoProductExpiredDate;
    public $infoProductBatch;
    public $infoProductAttachment;
    public $isMultipleCompany = false;

    // Helpers
    public $filterDisplayRackLabel = "Rak Display";
    public $filterWarehouseLabel = "Gudang";
    public $filterWarehouseMultipleLabel = "Gudang";
    public $companies = [];
    public $warehouses = [];
    public $displayRacks = [];
    public $locationTypeChoice = [];

    public function mount()
    {
        $this->loadUserState();
        $this->loadSetting();

        $this->filterCompany = $this->isMultipleCompany ? $this->filterCompany : false;
        $this->filterExpiredDateStart = $this->infoProductExpiredDate ? $this->filterExpiredDateStart : false;
        $this->filterExpiredDateEnd = $this->infoProductExpiredDate ? $this->filterExpiredDateEnd : false;

        if($this->filterLocationType)
        {
            $this->locationTypeChoice = [
                Warehouse::class => 'Gudang',
                DisplayRack::class => 'Rak Display',
            ];
            $this->locationType = Warehouse::class;
        }
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
            $this->warehouseId = $userState['warehouse_id'];
        }

        $this->displayRacks = DisplayRackRepository::all()->map(function ($item) {
            return [
                'id' => Crypt::encrypt($item->id),
                'name' => $item->name,
            ];
        })->toArray();
        $this->displayRackId = $this->displayRacks[0]['id'];
    }

    public function loadSetting()
    {
        $this->isMultipleCompany = SettingCore::get(SettingCore::MULTIPLE_COMPANY);

        $this->infoProductCode = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_CODE);
        $this->infoProductExpiredDate = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_EXPIRED_DATE);
        $this->infoProductBatch = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_BATCH);
        $this->infoProductAttachment = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_ATTACHMENT);
        
    }

    public function updated()
    {
        $this->dispatchFilter();
    }

    private function dispatchFilter()
    {
        $this->dispatch($this->dispatchEvent, [
            'companyId' => $this->companyId,
            'locationType' => $this->locationType,
            'displayRackId' => $this->displayRackId,
            // 'warehouseId' => $this->warehouseId,
            'warehouseIds' => $this->warehouseIds,
            'dateStart' => $this->dateStart,
            'dateEnd' => $this->dateEnd,
            'expiredDateStart' => $this->expiredDateStart,
            'expiredDateEnd' => $this->expiredDateEnd,
            'entryDateStart' => $this->entryDateStart,
            'entryDateEnd' => $this->entryDateEnd,
            'productId' => $this->productId,
            'productIds' => $this->productIds,
            'categoryProductIds' => $this->categoryProductIds,
        ]);
    }

    public function onSelect2Selected($var, $id)
    {
        $this->$var[] = $id;
        $this->dispatchFilter();
    }

    public function onSelect2Unselected($var, $id)
    {
        $index = array_search($id, $this->$var);
        if ($index !== false) {
            unset($this->$var[$index]);
            $this->dispatchFilter();
        }
    }

    public function render()
    {
        return view('livewire.logistic.filter');
    }
}
