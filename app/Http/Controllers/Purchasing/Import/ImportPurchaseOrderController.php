<?php

namespace App\Http\Controllers\Purchasing\Import;

use App\Http\Controllers\Controller;

class ImportPurchaseOrderController extends Controller
{
    public function index()
    {
        return view('app.purchasing.import.purchase-order.index');
    }
}
