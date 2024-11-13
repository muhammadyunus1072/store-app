<?php

namespace App\Http\Controllers\Logistic\Report;

use App\Http\Controllers\Controller;

class CurrentStockWarehouseController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.current-stock-warehouse.index');
    }
}
