<?php

namespace App\Http\Controllers\Purchasing\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        return view('app.purchasing.transaction.purchase-request.index');
    }

    public function create()
    {
        return view('app.purchasing.transaction.purchase-request.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.purchasing.transaction.purchase-request.detail', ["objId" => $request->id]);
    }
}
