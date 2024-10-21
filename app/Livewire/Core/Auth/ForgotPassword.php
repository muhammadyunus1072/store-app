<?php

namespace App\Livewire\Core\Auth;

use App\Helpers\General\Alert;
use App\Repositories\Core\User\UserRepository;
use Livewire\Component;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Validate;

class ForgotPassword extends Component
{
    #[Validate('required', message: 'Email Harus Diisi', onUpdate: false)]
    public $email;

    #[Validate('required', message: 'Captcha Harus Diisi', onUpdate: false)]
    #[Validate('captcha', message: 'Captcha Tidak Sesuai', onUpdate: false)]
    public $captcha;

    public function store()
    {
        $this->dispatch('reload-captcha');
        $this->validate();

        $user = UserRepository::findByEmail($this->email);
        if (empty($user)) {
            Alert::fail($this, 'Gagal', 'Email Belum Terdaftar');
            return;
        }

        $status = Password::sendResetLink(['email' => $this->email]);
        if ($status === Password::RESET_LINK_SENT) {
            Alert::success($this, 'Berhasil', 'Email Reset Password Telah Dikirim. Silahkan Periksa Email Anda');
            return;
        } else {
            Alert::fail($this, 'Gagal', 'Email Gagal Dikirim');
            return;
        }
    }

    public function render()
    {
        return view('livewire.core.auth.forgot-password');
    }
}
