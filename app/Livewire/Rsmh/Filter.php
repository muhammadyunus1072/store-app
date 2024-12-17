<?php

namespace App\Livewire\Purchasing;

use Livewire\Component;
use App\Helpers\Core\UserStateHandler;
use App\Settings\SettingCore;

class Filter extends Component
{
    public $prefixRoute = 'purchasing.filter.';
    public $dispatchEvent = 'datatable-add-filter';

    // Filter
    public $companyId;
    public $warehouseId;
    public $dateStart;
    public $dateEnd;
    public $supplierIds = [];
    public $productIds = [];
    public $categoryProductIds = [];

    // Setting Filter
    public $filterWarehouse;
    public $filterCompany;
    public $filterDateStart;
    public $filterDateEnd;
    public $filterSupplierMultiple;
    public $filterProductMultiple;
    public $filterCategoryProductMultiple;

    // Setting
    public $isMultipleCompany = false;

    // Helpers
    public $companies = [];
    public $warehouses = [];

    public function mount()
    {
        $this->loadUserState();
        $this->loadSetting();

        $this->filterCompany = $this->isMultipleCompany ? $this->filterCompany : false;
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
    }

    public function updated()
    {
        $this->dispatchFilter();
    }

    private function dispatchFilter()
    {
        $this->dispatch($this->dispatchEvent, [
            'companyId' => $this->companyId,
            'warehouseId' => $this->warehouseId,
            'dateStart' => $this->dateStart,
            'dateEnd' => $this->dateEnd,
            'productIds' => $this->productIds,
            'categoryProductIds' => $this->categoryProductIds,
            'supplierIds' => $this->supplierIds,
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
        return view('livewire.purchasing.filter');
    }
}
