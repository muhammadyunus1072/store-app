<?php

namespace App\Livewire\Core\Auth;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Validate;
use App\Helpers\General\Alert;
use App\Repositories\Core\User\UserRepository;

class ResetPassword extends Component
{
    public $token;

    #[Validate('required', message: 'Email Harus Diisi', onUpdate: false)]
    public $email;

    #[Validate('required', message: 'Password Baru Harus Diisi', onUpdate: false)]
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
        if (empty($user)) {
            Alert::fail($this, 'Gagal', 'Email Belum Terdaftar');
            return;
        }

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'token' => $this->token,
            ],
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ]);

                $user->email_verified_at = Carbon::now();
                $user->save();

                Auth::loginUsingId($user->id);
            }
        );


        if ($status === Password::PASSWORD_RESET) {
            Alert::success($this, 'Berhasil', 'Password Berhasil Diatur Ulang');
            $this->redirectRoute('dashboard.index');
        } else {
            Alert::fail($this, 'Gagal', 'Password Gagal Diatur Ulang');
        }
    }

    public function render()
    {
        return view('livewire.core.auth.reset-password');
    }
}
