<?php

namespace App\Http\Controllers\Sales\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CashierTransactionController extends Controller
{
    public function index()
    {
        return view('app.sales.transaction.cashier-transaction.index');
    }

    public function create()
    {
        return view('app.sales.transaction.cashier-transaction.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.sales.transaction.cashier-transaction.detail', ["objId" => $request->id]);
    }
}
