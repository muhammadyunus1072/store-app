<?php

namespace App\Livewire\Purchasing\Report;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Core\Setting\Setting;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\Core\UserStateHandler;
use App\Repositories\Core\Setting\SettingRepository;

class Filter extends Component
{
    // Setting Filter
    public $filterProduct;
    public $filterProductMultiple;
    public $filterCategoryProductMultiple;
    public $filterWarehouse;
    public $filterSupplier;
    public $show_input_entry_dateStart;
    public $show_input_entry_dateEnd;
    public $show_input_expired_dateStart;
    public $show_input_expired_dateEnd;
    public $filterDateStart;
    public $filterDateEnd;

    public $productIds = [];
    public $categoryProductIds = [];
    public $supplierIds;
    public $warehouse_id;
    public $entry_dateStart;
    public $entry_dateEnd;
    public $expired_dateStart;
    public $expired_dateEnd;
    public $dateStart;
    public $dateEnd;

    // Setting
    public $setting_product_code;
    public $setting_product_expired_date;
    public $setting_product_attachment;
    public $setting_product_batch;

    // Helpers
    public $isMultipleCompany = false;

    public $companies = [];
    public $warehouses = [];

    public $warehouseId;
    public $warehouseText;

    public $companyId;
    public $companyText;
    public $showExport = true;

    public function mount()
    {
        $this->dateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = Carbon::now()->endOfMonth()->format('Y-m-d');

        // $setting = SettingRepository::findByName(Setting::NAME_LOGISTIC);
        // $settings = json_decode($setting->setting);
        // $this->setting_product_code = $settings->product_code;
        // $this->setting_product_expired_date = $settings->product_expired_date;
        // $this->setting_product_attachment = $settings->product_attachment;
        // $this->setting_product_batch = $settings->product_batch;
        $this->loadUserState();
    }

    public function updated()
    {
        $this->filterHandle();
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

    private function filterHandle()
    {
        $this->dispatch('datatable-add-filter', [
            'productIds' => collect($this->productIds)
            ->pluck('id')
            ->map(function ($id) {
                return Crypt::decrypt($id);
            }),
            'categoryProductIds' => collect($this->categoryProductIds)
            ->pluck('id')
            ->map(function ($id) {
                return Crypt::decrypt($id);
            }),
            'supplierIds' => collect($this->supplierIds)
            ->pluck('id')
            ->map(function ($id) {
                return Crypt::decrypt($id);
            }),
            'warehouse_id' => $this->warehouse_id,
            'expired_dateStart' => $this->expired_dateStart,
            'expired_dateEnd' => $this->expired_dateEnd,
            'entry_dateStart' => $this->entry_dateStart,
            'entry_dateEnd' => $this->entry_dateEnd,
            'dateStart' => $this->dateStart,
            'dateEnd' => $this->dateEnd,

            'companyId' => $this->companyId,
            'warehouseId' => $this->warehouseId,
        ]);
    }
    public function resetEntryDateStart()
    {
        $this->entry_dateStart = null;
        $this->filterHandle();
    }
    public function resetEntryDateEnd()
    {
        $this->entry_dateEnd = null;
        $this->filterHandle();
    }
    public function resetExpiredDateStart()
    {
        $this->expired_dateStart = null;
        $this->filterHandle();
    }
    public function resetExpiredDateEnd()
    {
        $this->expired_dateEnd = null;
        $this->filterHandle();
    }


    public function selectCategoryProducts($data)
    {
        $this->categoryProductIds[] = [
            'id' => $data['id'],
            'text' => $data['text'],
        ];
        $this->filterHandle();
    }

    public function unselectCategoryProducts($data)
    {
        $index = array_search($data['id'], array_column($this->categoryProductIds, 'id'));
        if ($index !== false) {
            unset($this->categoryProductIds[$index]);
        }
        $this->filterHandle();
    }

    public function selectSuppliers($data)
    {
        $this->supplierIds[] = [
            'id' => $data['id'],
            'text' => $data['text'],
        ];
        $this->filterHandle();
    }

    public function unselectSuppliers($data)
    {
        $index = array_search($data['id'], array_column($this->supplierIds, 'id'));
        if ($index !== false) {
            unset($this->supplierIds[$index]);
        }
        $this->filterHandle();
    }

    public function selectProducts($data)
    {
        $this->productIds[] = [
            'id' => $data['id'],
            'text' => $data['text'],
        ];
        $this->filterHandle();
    }

    public function unselectProducts($data)
    {
        $index = array_search($data['id'], array_column($this->productIds, 'id'));
        if ($index !== false) {
            unset($this->productIds[$index]);
        }
        $this->filterHandle();
    }

    public function render()
    {
        return view('livewire.purchasing.report.filter');
    }
}
