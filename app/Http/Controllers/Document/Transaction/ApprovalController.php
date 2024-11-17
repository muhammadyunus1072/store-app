<?php

namespace App\Http\Controllers\Document\Transaction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApprovalController extends Controller
{
    public function index()
    {
        return view('app.document.transaction.approval.index');
    }

    public function create()
    {
        return view('app.document.transaction.approval.detail', ["objId" => null]);
    }

    public function show(Request $request)
    {
        return view('app.document.transaction.approval.detail', ["objId" => $request->id]);
    }
}
