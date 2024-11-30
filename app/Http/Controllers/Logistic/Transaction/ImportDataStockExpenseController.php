<?php

namespace App\Http\Controllers\Logistic\Transaction;

use App\Http\Controllers\Controller;

class ImportDataStockExpenseController extends Controller
{
    public function index()
    {
        return view('app.logistic.transaction.import-data-stock-expense.index');
    }
}
