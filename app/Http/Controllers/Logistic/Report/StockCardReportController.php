<?php

namespace App\Http\Controllers\Logistic\Report;

use App\Http\Controllers\Controller;

class StockCardReportController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.stock-card.index');
    }
}
