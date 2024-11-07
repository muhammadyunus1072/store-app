<?php

namespace App\Http\Controllers\Logistic\Report;

use App\Http\Controllers\Controller;

class CurrentStockReportController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.current-stock.index');
    }
}
