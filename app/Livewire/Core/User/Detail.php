<?php

namespace App\Livewire\Core\User;

use Exception;
use App\Helpers\General\Alert;
use App\Repositories\Core\Setting\SettingRepository;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Core\User\RoleRepository;
use App\Repositories\Core\User\UserRepository;
use App\Repositories\Core\User\UserCompanyRepository;
use App\Repositories\Core\User\UserWarehouseRepository;
use App\Settings\SettingCore;

class Detail extends Component
{
    public $objId;

    #[Validate('required', message: 'Nama Harus Diisi', onUpdate: false)]
    public $name;

    #[Validate('required', message: 'Username Harus Diisi', onUpdate: false)]
    public $username;

    #[Validate('required', message: 'Email Harus Diisi', onUpdate: false)]
    #[Validate('email', message: "Format Email Tidak Sesuai", onUpdate: false)]
    public $email;

    #[Validate('required', message: 'Jabatan Harus Dipilih', onUpdate: false)]
    public $role;

    public $password;

    public $userCompanies = [];
    public $userWarehouses = [];

    // Helpers
    public $isMultipleCompany = false;
    public $roles = [];

    public function mount()
    {
        $this->isMultipleCompany = SettingCore::get(SettingCore::MULTIPLE_COMPANY);

        $this->roles = RoleRepository::getIdAndNames()->pluck('name');
        $this->role = $this->roles[0];

        if ($this->objId) {
            $objId = Crypt::decrypt($this->objId);
            $user = UserRepository::findWithDetails($objId);

            $this->name = $user->name;
            $this->username = $user->username;
            $this->email = $user->email;
            $this->role = $user->roles[0]->name;

            foreach ($user->userCompanies as $item) {
                $this->userCompanies[] = [
                    'id' => Crypt::encrypt($item->company_id),
                    'text' => $item->company->name,
                ];
            }

            foreach ($user->userWarehouses as $item) {
                $this->userWarehouses[] = [
                    'id' => Crypt::encrypt($item->warehouse_id),
                    'text' => $item->warehouse->name,
                ];
            }
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            return;
        }

        $this->name = "";
        $this->username = "";
        $this->email = "";
        $this->role = $this->roles[0];
        $this->password = "";
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('user.index');
    }

    public function selectCompany($data)
    {
        $this->userCompanies[] = [
            'id' => $data['id'],
            'text' => $data['text'],
        ];
    }

    public function unselectCompany($data)
    {
        $index = array_search($data['id'], array_column($this->userCompanies, 'id'));
        if ($index !== false) {
            unset($this->userCompanies[$index]);
        }
    }

    public function selectWarehouse($data)
    {
        $this->userWarehouses[] = [
            'id' => $data['id'],
            'text' => $data['text'],
        ];
    }

    public function unselectWarehouse($data)
    {
        $index = array_search($data['id'], array_column($this->userWarehouses, 'id'));
        if ($index !== false) {
            unset($this->userWarehouses[$index]);
        }
    }

    public function store()
    {
        $this->validate();

        if (!$this->objId) {
            $otherUser = UserRepository::findByEmail($this->email);
            if (!empty($otherUser) && $otherUser->id != $this->objId) {
                Alert::fail($this, "Gagal", "Email telah digunakan pada akun yang lainnya. Silahkan gunakan email lain.");
                return;
            }

            $otherUser = UserRepository::findByUsername($this->username);
            if (!empty($otherUser) && $otherUser->id != $this->objId) {
                Alert::fail($this, "Gagal", "Username telah digunakan pada akun yang lainnya. Silahkan gunakan email lain.");
                return;
            }
        }

        if (empty($this->objId) && empty($this->password)) {
            Alert::fail($this, "Gagal", "Password Harus Diisi");
            return;
        }

        $validatedData = [
            'username' => $this->username,
            'name' => $this->name,
            'email' => $this->email,
        ];
        if (!empty($this->password)) {
            $validatedData['password'] = Hash::make($this->password);
        }

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                UserRepository::update($objId, $validatedData);
                $user = UserRepository::find($objId);
                $user->syncRoles($this->role);
            } else {
                $user = UserRepository::create($validatedData);
                $user->assignRole($this->role);
                $objId = $user->id;
            }

            // Handle User Companies
            foreach ($this->userCompanies as $item) {
                UserCompanyRepository::createIfNotExist([
                    'user_id' => $objId,
                    'company_id' => Crypt::decrypt($item['id']),
                ]);
            }
            UserCompanyRepository::deleteExcept($objId, array_map(function ($item) {
                return Crypt::decrypt($item['id']);
            }, $this->userCompanies));

            // Handle User Warehouses
            foreach ($this->userWarehouses as $item) {
                UserWarehouseRepository::createIfNotExist([
                    'user_id' => $objId,
                    'warehouse_id' => Crypt::decrypt($item['id']),
                ]);
            }
            UserWarehouseRepository::deleteExcept($objId, array_map(function ($item) {
                return Crypt::decrypt($item['id']);
            }, $this->userWarehouses));

            DB::commit();

            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                "Berhasil",
                "Pengguna Berhasil Diperbarui",
                "on-dialog-confirm",
                "on-dialog-cancel",
                "Oke",
                "Tutup",
            );
        } catch (Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.core.user.detail');
    }
}
