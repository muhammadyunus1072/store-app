<?php

namespace App\Livewire\Core\Auth;

use App\Helpers\Alert;
use App\Repositories\Core\User\UserRepository;
use Livewire\Component;
use Livewire\Attributes\Validate;

class EmailVerification extends Component
{
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
            Alert::fail($this, 'Kirim Ulang Email Gagal', 'Email Belum Terdaftar');
            return;
        }

        if ($user->email_verified_at) {
            Alert::success($this, 'Berhasil', 'Email Telah Terverifikasi');
            $this->redirectRoute('login');
            return;
        }

        $user->sendEmailVerificationNotification();
        $this->dispatch('disabled-send-email');
        Alert::success($this, 'Kirim Ulang Email', 'Email berhasil dikirimkan ulang. Silahkan Periksa Email Anda');
    }

    public function render()
    {
        return view('livewire.core.auth.email-verification');
    }
}
