<?php

namespace App\Livewire\Core\Auth;

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Helpers\General\Alert;
use App\Repositories\Core\User\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Register extends Component
{
    #[Validate('required', message: 'Nama Harus Diisi', onUpdate: false)]
    public $name;

    #[Validate('required', message: 'Username Harus Diisi', onUpdate: false)]
    public $username;

    #[Validate('required', message: 'Email Harus Diisi', onUpdate: false)]
    #[Validate('email', message: "Format Email Tidak Sesuai", onUpdate: false)]
    public $email;

    #[Validate('required', message: 'Password Harus Diisi', onUpdate: false)]
    public $password;

    #[Validate('required', message: 'Ketik Ulang Password Harus Diisi', onUpdate: false)]
    public $retypePassword;

    #[Validate('required', message: 'Captcha Harus Diisi', onUpdate: false)]
    #[Validate('captcha', message: 'Captcha Tidak Sesuai', onUpdate: false)]
    public $captcha;

    public function store()
    {
        $this->dispatch('reload-captcha');
        $this->validate();

        if ($this->password != $this->retypePassword) {
            Alert::fail($this, 'Register Gagal', 'Pengetikan Ulang Password Tidak Sama');
            return;
        }

        $user = UserRepository::findByEmail($this->email);
        if (!empty($user)) {
            Alert::fail($this, 'Register Gagal', 'Email Sudah Digunakan');
            return;
        }

        $user = UserRepository::findByUsername($this->username);
        if (!empty($user)) {
            Alert::fail($this, 'Register Gagal', 'Username Sudah Digunakan');
            return;
        }

        $user = UserRepository::create([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);
        $user->assignRole(config('template.registration_default_role'));

        if (config('template.email_verification_route')) {
            $user->sendEmailVerificationNotification();
            $this->redirectRoute('verification.index', ['email' => $this->email]);
            return;
        }

        Auth::loginUsingId($user->id);
        $this->redirectRoute('login');
    }

    public function render()
    {
        return view('livewire.core.auth.register');
    }
}
