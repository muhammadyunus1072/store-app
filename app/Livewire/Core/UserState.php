<?php

namespace App\Livewire\Core;

use App\Helpers\Core\UserStateHelper;
use App\Repositories\Core\Company\CompanyRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;
use Illuminate\Support\Facades\Crypt;
use Livewire\Component;

class UserState extends Component
{
    public $arrangement = 'horizontal';

    public $companyId;
    public $warehouseId;

    public $companies = [];
    public $warehouses = [];

    public function mount()
    {
        // User State
        $userState = UserStateHelper::get();
        $userCompanyId = $userState['company_id'];
        $userWarehouseId = $userState['warehouse_id'];

        // Company Options
        $this->companies = CompanyRepository::getByUser($userState['user_id'])->toArray();
        foreach ($this->companies as $index => $company) {
            $this->companies[$index]['id'] = Crypt::encrypt($company['id']);

            if ($userCompanyId == $company['id']) {
                $this->companyId = $this->companies[$index]['id'];
            }
        }

        if (empty($this->companyId) && count($this->companies) > 0) {
            $this->companyId = $this->companies[0]['id'];
            $updateUserState['company_id'] = Crypt::decrypt($this->companyId);
        }

        // Warehouse Options
        $this->warehouses = WarehouseRepository::getByCompany(Crypt::decrypt($this->companyId))->toArray();
        foreach ($this->warehouses as $index => $warehouse) {
            $this->warehouses[$index]['id'] = Crypt::encrypt($warehouse['id']);

            if ($userWarehouseId == $warehouse['id']) {
                $this->warehouseId = $this->warehouses[$index]['id'];
            }
        }

        if (empty($this->warehouseId) && count($this->warehouses) > 0) {
            $this->warehouseId = $this->warehouses[0]['id'];
            $updateUserState['warehouse_id'] = Crypt::decrypt($this->warehouseId);
        }

        // Update User State
        if (isset($updateUserState)) {
            UserStateHelper::save([
                'company_id' => $this->companyId ? Crypt::decrypt($this->companyId) : null,
                'warehouse_id' => $this->warehouseId ? Crypt::decrypt($this->warehouseId) : null,
            ]);
        }
    }

    public function updated()
    {
        UserStateHelper::save([
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
