<?php

namespace App\Http\Controllers\Logistic\Report;

use App\Http\Controllers\Controller;

class StockCardDetailReportController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.stock-card-detail.index');
    }
}
