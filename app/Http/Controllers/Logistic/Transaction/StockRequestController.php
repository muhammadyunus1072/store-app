<?php

namespace App\Http\Controllers\Logistic\Transaction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StockRequestController extends Controller
{
    public function index()
    {
        return view('app.logistic.transaction.stock-request.index');
    }

    public function create()
    {
        return view('app.logistic.transaction.stock-request.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.logistic.transaction.stock-request.detail', ["objId" => $request->id]);
    }

    public function show(Request $request)
    {
        return view('app.logistic.transaction.stock-request.show', ["objId" => $request->id]);
    }
}
