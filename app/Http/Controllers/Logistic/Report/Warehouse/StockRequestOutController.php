<?php

namespace App\Http\Controllers\Logistic\Report\Warehouse;

use App\Http\Controllers\Controller;

class StockRequestOutController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.warehouse.stock-request-out.index');
    }
}
