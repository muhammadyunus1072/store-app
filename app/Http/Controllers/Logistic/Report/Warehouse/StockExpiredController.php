<?php

namespace App\Http\Controllers\Logistic\Report\Warehouse;

use App\Http\Controllers\Controller;

class StockExpiredController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.warehouse.stock-expired.index');
    }
}
