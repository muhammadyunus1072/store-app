<?php

namespace App\Http\Controllers\Logistic\Transaction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Logistic\Transaction\StockOpname\StockOpname;

class StockOpnameController extends Controller
{
    public function index()
    {
        return view('app.logistic.transaction.stock-opname.index');
    }

    public function create()
    {
        return view('app.logistic.transaction.stock-opname.detail', ["objId" => null, "objClass" => StockOpname::class, 'isShow' => 0]);
    }

    public function edit(Request $request)
    {
        return view('app.logistic.transaction.stock-opname.detail', ["objId" => $request->id, "objClass" => StockOpname::class, 'isShow' => 0]);
    }

    public function show(Request $request)
    {
        return view('app.logistic.transaction.stock-opname.detail', ["objId" => $request->id, "objClass" => StockOpname::class, 'isShow' => 1]);
    }
}
