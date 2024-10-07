<?php

namespace App\Http\Controllers\Logistic\Transaction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StockExpenseController extends Controller
{
    public function index()
    {
        return view('app.logistic.transaction.stock-expense.index');
    }

    public function create()
    {
        return view('app.logistic.transaction.stock-expense.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.logistic.transaction.stock-expense.detail', ["objId" => $request->id]);
    }

    public function show(Request $request)
    {
        return view('app.logistic.transaction.stock-expense.show', ["objId" => $request->id]);
    }
}
