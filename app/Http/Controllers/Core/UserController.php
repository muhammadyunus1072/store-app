<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Core\User\UserRepository;

class UserController extends Controller
{
    public function index()
    {
        return view('app.core.user.index');
    }

    public function create()
    {
        return view('app.core.user.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.core.user.detail', ["objId" => $request->id]);
    }
    // Select2
    // User
    public function search(Request $request)
    {
        return UserRepository::search($request->search);
    }
}
