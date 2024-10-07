<?php

namespace App\Http\Controllers\Document\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StatusApprovalController extends Controller
{
    public function index()
    {
        return view('app.document.master.status-approval.index');
    }

    public function create()
    {
        return view('app.document.master.status-approval.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.document.master.status-approval.detail', ["objId" => $request->id]);
    }
}
