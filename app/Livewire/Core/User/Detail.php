<?php

namespace App\Livewire\Core\User;

use Exception;
use App\Helpers\Alert;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Core\User\RoleRepository;
use App\Repositories\Core\User\UserRepository;
use App\Repositories\Core\User\UserCompanyRepository;

class Detail extends Component
{
    public $objId;

    public $roles = [];

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

    public function mount()
    {
        $this->roles = RoleRepository::getIdAndNames()->pluck('name');
        $this->role = $this->roles[0];

        if ($this->objId) {
            $objId = Crypt::decrypt($this->objId);
            $user = UserRepository::findWithDetails($objId);

            $this->name = $user->name;
            $this->username = $user->username;
            $this->email = $user->email;
            $this->role = $user->roles[0]->name;

            foreach ($user->userCompanies as $user_company) {
                $this->userCompanies[] = [
                    'id' => Crypt::encrypt($user_company->company_id),
                    'text' => $user_company->company->name,
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

    public function store()
    {
        if (count($this->userCompanies) == 0) {
            Alert::fail($this, "Gagal", "Perusahaan Belum Diinput");
            return;
        }
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

            foreach ($this->userCompanies as $user_company) {
                UserCompanyRepository::createIfNotExist([
                    'user_id' => $objId,
                    'company_id' => Crypt::decrypt($user_company['id']),
                ]);
            }
            UserCompanyRepository::deleteExcept($objId, array_map(function ($item) {
                return Crypt::decrypt($item['id']);
            }, $this->userCompanies));

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
