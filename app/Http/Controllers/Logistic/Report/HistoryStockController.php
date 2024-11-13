<?php

namespace App\Http\Controllers\Logistic\Report;

use App\Http\Controllers\Controller;

class HistoryStockController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.history-stock.index');
    }
}
