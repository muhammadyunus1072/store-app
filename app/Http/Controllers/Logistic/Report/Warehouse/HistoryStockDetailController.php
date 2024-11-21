<?php

namespace App\Http\Controllers\Logistic\Report\Warehouse;

use App\Http\Controllers\Controller;

class HistoryStockDetailController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.warehouse.history-stock-detail.index');
    }
}
