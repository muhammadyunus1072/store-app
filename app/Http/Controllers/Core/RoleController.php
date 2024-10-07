<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return view('app.core.role.index');
    }

    public function create()
    {
        return view('app.core.role.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.core.role.detail', ["objId" => $request->id]);
    }
}
