<?php

namespace App\Http\Controllers\Logistic\Report;

use App\Http\Controllers\Controller;

class StockCardWarehouseReportController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.stock-card-warehouse.index');
    }
}
