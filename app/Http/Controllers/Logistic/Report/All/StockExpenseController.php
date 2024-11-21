<?php

namespace App\Http\Controllers\Logistic\Report\All;

use App\Http\Controllers\Controller;

class StockExpenseController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.all.stock-expense.index');
    }
}
