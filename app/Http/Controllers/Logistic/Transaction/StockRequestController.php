<?php

namespace App\Http\Controllers\Logistic\Transaction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Logistic\Transaction\StockRequest\StockRequest;

class StockRequestController extends Controller
{
    public function index()
    {
        return view('app.logistic.transaction.stock-request.index');
    }

    public function create()
    {
        return view('app.logistic.transaction.stock-request.detail', ["objId" => null, "objClass" => StockRequest::class, 'isShow' => 0]);
    }

    public function edit(Request $request)
    {
        return view('app.logistic.transaction.stock-request.detail', ["objId" => $request->id, "objClass" => StockRequest::class, 'isShow' => 0]);
    }

    public function show(Request $request)
    {
        return view('app.logistic.transaction.stock-request.detail', ["objId" => $request->id, "objClass" => StockRequest::class, 'isShow' => 1]);
    }
}
