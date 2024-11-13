<?php

namespace App\Http\Controllers\Logistic\Report;

use App\Http\Controllers\Controller;

class CurrentStockDetailWarehouseController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.current-stock-detail-warehouse.index');
    }
}
