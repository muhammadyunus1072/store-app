<?php

namespace App\Livewire\Core\Auth;

use App\Helpers\General\Alert;
use App\Repositories\Core\User\UserRepository;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;

class Login extends Component
{
    #[Validate('required', message: 'Username / Email Harus Diisi', onUpdate: false)]
    public $usernameOrEmail;

    #[Validate('required', message: 'Password Harus Diisi', onUpdate: false)]
    public $password;

    #[Validate('required', message: 'Captcha Harus Diisi', onUpdate: false)]
    #[Validate('captcha', message: 'Captcha Tidak Sesuai', onUpdate: false)]
    public $captcha;

    public function store()
    {
        $this->dispatch('reload-captcha');
        $this->validate();

        $user = UserRepository::findByUsernameOrEmail($this->usernameOrEmail);
        if (empty($user)) {
            Alert::fail($this, 'Login Gagal', 'Akun Belum Terdaftar');
            return;
        }

        if (!Hash::check($this->password, $user->password)) {
            Alert::fail($this, 'Login Gagal', 'Password Tidak Sesuai');
            return;
        }

        if (empty($user->email_verified_at) && config('template.email_verification_route')) {
            $user->sendEmailVerificationNotification();
            $this->redirectRoute('verification.index', ['email' => $user->email]);
            return;
        }

        Auth::loginUsingId($user->id);
        $this->redirectRoute('dashboard.index');
    }

    public function render()
    {
        return view('livewire.core.auth.login');
    }
}
