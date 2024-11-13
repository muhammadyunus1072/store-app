<?php

namespace App\Http\Controllers\Logistic\Report;

use App\Http\Controllers\Controller;

class HistoryStockWarehouseController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.history-stock-warehouse.index');
    }
}
