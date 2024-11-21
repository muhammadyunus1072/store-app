<?php

namespace App\Http\Controllers\Logistic\Report\Warehouse;

use App\Http\Controllers\Controller;

class StockExpenseController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.warehouse.stock-expense.index');
    }
}
