<?php

namespace App\Livewire\Logistic\Report;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\Core\UserStateHandler;
use App\Settings\SettingCore;
use App\Settings\SettingLogistic;

class Filter extends Component
{
    public $dispatchEvent = 'datatable-add-filter';

    // Filter
    public $dateStart;
    public $dateEnd;
    public $entryDateStart;
    public $entryDateEnd;
    public $expiredDateStart;
    public $expiredDateEnd;
    public $productId;
    public $productIds = [];
    public $categoryProductIds = [];
    public $companyId;
    public $warehouseId;

    // Setting Filter
    public $filterWarehouse;
    public $filterCompany;
    public $filterProduct;
    public $filterProductMultiple;
    public $filterCategoryProductMultiple;
    public $filterSupplierMultiple;
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
    public $showExport = true;

    // Helpers
    public $companies = [];
    public $warehouses = [];

    public function mount()
    {
        $this->loadUserState();
        $this->loadSetting();

        $this->dateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->filterCompany = $this->isMultipleCompany ? $this->filterCompany : false;
        $this->filterExpiredDateStart = $this->infoProductExpiredDate ? $this->filterExpiredDateStart : false;
        $this->filterExpiredDateEnd = $this->infoProductExpiredDate ? $this->filterExpiredDateEnd : false;
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
            'productIds' => collect($this->productIds)->map(function ($id) {
                return Crypt::decrypt($id);
            }),
            'categoryProductIds' => collect($this->categoryProductIds)->map(function ($id) {
                return Crypt::decrypt($id);
            }),
            'productId' => $this->productId ? Crypt::decrypt($this->productId) : null,
            'companyId' => $this->companyId ? Crypt::decrypt($this->companyId) : null,
            'warehouseId' => $this->warehouseId ? Crypt::decrypt($this->warehouseId) : null,
            'dateStart' => $this->dateStart,
            'dateEnd' => $this->dateEnd,
            'expiredDateStart' => $this->expiredDateStart,
            'expiredDateEnd' => $this->expiredDateEnd,
            'entryDateStart' => $this->entryDateStart,
            'entryDateEnd' => $this->entryDateEnd,
        ]);
    }

    /* 
    | SELECT2 CATEGORY PRODUCT MULTIPLE
    */
    public function onSelectCategoryProduct($id)
    {
        $this->categoryProductIds[] = $id;
        $this->dispatchFilter();
    }

    public function onUnselectCategoryProduct($id)
    {
        $index = array_search($id['id'], $this->categoryProductIds);
        if ($index !== false) {
            unset($this->categoryProductIds[$index]);
            $this->dispatchFilter();
        }
    }

    /* 
    | SELECT2 PRODUCT MULTIPLE
    */
    public function onSelectProduct($id)
    {
        $this->productIds[] = $id;
        $this->dispatchFilter();
    }

    public function onUnselectProduct($id)
    {
        $index = array_search($id, $this->productIds);
        if ($index !== false) {
            unset($this->productIds[$index]);
            $this->dispatchFilter();
        }
    }

    public function render()
    {
        return view('livewire.logistic.report.filter');
    }
}
