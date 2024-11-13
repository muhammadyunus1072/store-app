<?php

namespace App\Http\Controllers\Purchasing\Report;

use App\Http\Controllers\Controller;

class PurchaseOrderProductDetailController extends Controller
{
    public function index()
    {
        return view('app.purchasing.report.purchase-order-product-detail.index');
    }
}
