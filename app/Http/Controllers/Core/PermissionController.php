<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return view('app.core.permission.index');
    }

    public function create()
    {
        return view('app.core.permission.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.core.permission.detail', ["objId" => $request->id]);
    }
}
