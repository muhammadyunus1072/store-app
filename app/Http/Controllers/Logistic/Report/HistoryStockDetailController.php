<?php

namespace App\Http\Controllers\Logistic\Report;

use App\Http\Controllers\Controller;

class HistoryStockDetailController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.history-stock-detail.index');
    }
}
