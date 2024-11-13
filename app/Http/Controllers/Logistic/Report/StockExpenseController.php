<?php

namespace App\Http\Controllers\Logistic\Report;

use App\Http\Controllers\Controller;

class StockExpenseController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.stock-expense.index');
    }
}
