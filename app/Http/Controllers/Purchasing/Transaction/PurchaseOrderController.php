<?php

namespace App\Http\Controllers\Purchasing\Transaction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrder;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        return view('app.purchasing.transaction.purchase-order.index');
    }

    public function create()
    {
        return view('app.purchasing.transaction.purchase-order.detail', ["objId" => null, "objClass" => PurchaseOrder::class, 'isShow' => 0]);
    }

    public function edit(Request $request)
    {
        return view('app.purchasing.transaction.purchase-order.detail', ["objId" => $request->id, "objClass" => PurchaseOrder::class, 'isShow' => 0]);
    }

    public function show(Request $request)
    {
        return view('app.purchasing.transaction.purchase-order.detail', ["objId" => $request->id, "objClass" => PurchaseOrder::class, 'isShow' => 1]);
    }
}
