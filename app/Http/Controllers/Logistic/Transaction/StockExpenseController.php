<?php

namespace App\Http\Controllers\Logistic\Transaction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Logistic\Transaction\StockExpense\StockExpense;

class StockExpenseController extends Controller
{
    public function index()
    {
        return view('app.logistic.transaction.stock-expense.index');
    }

    public function create()
    {
        return view('app.logistic.transaction.stock-expense.detail', ["objId" => null, "objClass" => StockExpense::class, 'isShow' => 0]);
    }

    public function edit(Request $request)
    {
        return view('app.logistic.transaction.stock-expense.detail', ["objId" => $request->id, "objClass" => StockExpense::class, 'isShow' => 0]);
    }

    public function show(Request $request)
    {
        return view('app.logistic.transaction.stock-expense.detail', ["objId" => $request->id, "objClass" => StockExpense::class, 'isShow' => 1]);
    }
}
