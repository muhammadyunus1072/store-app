<?php

namespace App\Http\Controllers\Purchasing\Transaction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        return view('app.purchasing.transaction.purchase-order.index');
    }

    public function create()
    {
        return view('app.purchasing.transaction.purchase-order.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.purchasing.transaction.purchase-order.detail', ["objId" => $request->id]);
    }

    public function show(Request $request)
    {
        return view('app.purchasing.transaction.purchase-order.show', ["objId" => $request->id]);
    }
}
