<?php

namespace App\Http\Controllers\Core;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Repositories\Core\User\UserRepository;

class AuthController extends Controller
{
    public function index()
    {
        return view('app.core.auth.login');
    }

    public function login()
    {
        return view('app.core.auth.login');
    }

    public function register()
    {
        return view('app.core.auth.register');
    }

    public function forgotPassword()
    {
        return view('app.core.auth.forgot-password');
    }

    public function resetPassword(Request $request)
    {
        return view('app.core.auth.reset-password', ['token' => $request->token, 'email' => $request->email]);
    }

    public function emailVerification(Request $request)
    {
        return view('app.core.auth.email-verification', ['email' => $request->email]);
    }

    public function profile()
    {
        return view('app.core.auth.profile');
    }

    public function emailVerificationVerify(Request $request)
    {
        $user = UserRepository::find($request->id);
        if (!hash_equals(sha1($user->getEmailForVerification()), (string) $request->hash)) {
            return redirect()->route('login');
        }

        $user->email_verified_at = Carbon::now();
        $user->save();

        Auth::loginUsingId($user->id);

        return redirect()->route('dashboard.index');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
