<?php

namespace App\Http\Controllers\Logistic\Report;

use App\Http\Controllers\Controller;

class StockExpenseWarehouseController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.stock-expense-warehouse.index');
    }
}
