<?php

namespace App\Livewire\Core;

use App\Helpers\Core\UserStateHandler;
use App\Repositories\Core\Company\CompanyRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;
use App\Settings\SettingCore;
use Illuminate\Support\Facades\Crypt;
use Livewire\Component;

class UserState extends Component
{
    public $companyId;
    public $warehouseId;

    public $companies = [];
    public $warehouses = [];

    // Helpers
    public $isMultipleCompany = false;

    public function mount()
    {
        $this->isMultipleCompany = SettingCore::get(SettingCore::MULTIPLE_COMPANY);

        // User State
        $userState = UserStateHandler::get();

        $this->companies = $userState['companies'];
        $this->companyId = $userState['company_id'];
        $this->warehouses = $userState['warehouses'];
        $this->warehouseId = $userState['warehouse_id'];
    }

    public function updated()
    {
        UserStateHandler::set([
            'company_id' => $this->companyId ? Crypt::decrypt($this->companyId) : null,
            'warehouse_id' => $this->warehouseId ? Crypt::decrypt($this->warehouseId) : null,
        ]);

        $this->dispatch('refresh-page');
    }

    public function render()
    {
        return view('livewire.core.user-state');
    }
}
