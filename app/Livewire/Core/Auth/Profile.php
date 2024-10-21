<?php

namespace App\Livewire\Core\Auth;

use Exception;
use App\Helpers\General\Alert;
use App\Repositories\Core\User\UserRepository;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;

class Profile extends Component
{
    #[Validate('required', message: 'Nama Harus Diisi', onUpdate: false)]
    public $name;

    public $email;
    public $role;

    public $oldPassword;
    public $password;
    public $retypePassword;

    public function mount()
    {
        $user = UserRepository::authenticatedUser();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles[0]->name;
    }

    public function store()
    {
        $this->validate();

        $user = UserRepository::authenticatedUser();

        $validatedData = [
            'name' => $this->name,
        ];

        if (!empty($this->oldPassword) || !empty($this->password) || !empty($this->retypePassword)) {
            if (empty($this->oldPassword)) {
                Alert::fail($this, "Gagal", "Password Lama Harus Diisi");
                return;
            }
            if (empty($this->password)) {
                Alert::fail($this, "Gagal", "Password Baru Harus Diisi");
                return;
            }
            if (empty($this->retypePassword)) {
                Alert::fail($this, "Gagal", "Ketik Ulang Password Baru Harus Diisi");
                return;
            }
            if ($this->retypePassword != $this->password) {
                Alert::fail($this, "Gagal", "Pengetikan Ulang Password Baru Tidak Sama");
                return;
            }
            if (!Hash::check($this->oldPassword, $user->password)) {
                Alert::fail($this, 'Gagal', 'Password Lama Tidak Sesuai');
                return;
            }

            $validatedData['password'] = Hash::make($this->password);
        }

        try {
            DB::beginTransaction();
            UserRepository::update($user->id, $validatedData);
            Alert::success($this, 'Berhasil', 'Profil berhasil diperbarui');
            DB::commit();

            $this->redirectRoute('profile');
        } catch (Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.core.auth.profile');
    }
}
