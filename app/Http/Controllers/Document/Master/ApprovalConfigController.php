<?php

namespace App\Http\Controllers\Document\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApprovalConfigController extends Controller
{
    public function index()
    {
        return view('app.document.master.approval-config.index');
    }

    public function create()
    {
        return view('app.document.master.approval-config.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.document.master.approval-config.detail', ["objId" => $request->id]);
    }
}
