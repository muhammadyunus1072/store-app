<?php

namespace App\Http\Controllers\Logistic\Report;

use App\Exports\CollectionExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\Logistic\Report\CurrentStockDetail\CurrentStockDetailRepository;

class CurrentStockDetailController extends Controller
{
    public function index()
    {
        return view('app.logistic.report.current-stock-detail.index');
    }
}
