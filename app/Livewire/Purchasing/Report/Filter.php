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
    public $show_input_product;
    public $show_input_category_product;
    public $show_input_warehouse;
    public $show_input_supplier;
    public $show_input_entry_date_start;
    public $show_input_entry_date_end;
    public $show_input_expired_date_start;
    public $show_input_expired_date_end;
    public $show_input_date_start;
    public $show_input_date_end;

    public $products = [];
    public $category_products = [];
    public $warehouse_id;
    public $supplier_id;
    public $entry_date_start;
    public $entry_date_end;
    public $expired_date_start;
    public $expired_date_end;
    public $date_start;
    public $date_end;

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
    
    public function mount()
    {
        $this->date_start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->date_end = Carbon::now()->endOfMonth()->format('Y-m-d');

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
            'products' => collect($this->products)
            ->pluck('id')
            ->map(function ($id) {
                return Crypt::decrypt($id);
            }),
            'category_products' => collect($this->category_products)
            ->pluck('id')
            ->map(function ($id) {
                return Crypt::decrypt($id);
            }),
            'warehouse_id' => $this->warehouse_id,
            'supplier_id' => $this->supplier_id,
            'expired_date_start' => $this->expired_date_start,
            'expired_date_end' => $this->expired_date_end,
            'entry_date_start' => $this->entry_date_start,
            'entry_date_end' => $this->entry_date_end,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,

            'companyId' => $this->companyId,
            'warehouseId' => $this->warehouseId,
        ]);
    }
    public function resetEntryDateStart()
    {
        $this->entry_date_start = null;
        $this->filterHandle();
    }
    public function resetEntryDateEnd()
    {
        $this->entry_date_end = null;
        $this->filterHandle();
    }
    public function resetExpiredDateStart()
    {
        $this->expired_date_start = null;
        $this->filterHandle();
    }
    public function resetExpiredDateEnd()
    {
        $this->expired_date_end = null;
        $this->filterHandle();
    }


    public function selectCategoryProducts($data)
    {
        $this->category_products[] = [
            'id' => $data['id'],
            'text' => $data['text'],
        ];
        $this->filterHandle();
    }

    public function unselectCategoryProducts($data)
    {
        $index = array_search($data['id'], array_column($this->category_products, 'id'));
        if ($index !== false) {
            unset($this->category_products[$index]);
        }
        $this->filterHandle();
    }

    public function selectProducts($data)
    {
        $this->products[] = [
            'id' => $data['id'],
            'text' => $data['text'],
        ];
        $this->filterHandle();
    }

    public function unselectProducts($data)
    {
        $index = array_search($data['id'], array_column($this->products, 'id'));
        if ($index !== false) {
            unset($this->products[$index]);
        }
        $this->filterHandle();
    }

    public function render()
    {
        return view('livewire.purchasing.report.filter');
    }
}
