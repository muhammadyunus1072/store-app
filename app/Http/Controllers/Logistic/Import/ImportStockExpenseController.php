<?php

namespace App\Http\Controllers\Logistic\Import;

use App\Http\Controllers\Controller;

class ImportStockExpenseController extends Controller
{
    public function index()
    {
        return view('app.logistic.import.stock-expense.index');
    }
}
