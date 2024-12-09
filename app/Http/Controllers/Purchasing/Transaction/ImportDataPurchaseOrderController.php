<?php

namespace App\Http\Controllers\Purchasing\Transaction;

use App\Http\Controllers\Controller;

class ImportDataPurchaseOrderController extends Controller
{
    public function index()
    {
        return view('app.purchasing.transaction.import-data-purchase-order.index');
    }
}
