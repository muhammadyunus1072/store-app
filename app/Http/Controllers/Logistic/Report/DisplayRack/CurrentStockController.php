<?php

namespace App\Http\Controllers\Logistic\Report\DisplayRack;

use App\Http\Controllers\Controller;

class CurrentStockController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.display-rack.current-stock.index');
    }
}
