<?php

namespace App\Http\Controllers\Logistic\Transaction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductDetailController extends Controller
{
    public function index()
    {
        return view('app.logistic.transaction.product-detail.index');
    }

    public function create()
    {
        return view('app.logistic.transaction.product-detail.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.logistic.transaction.product-detail.detail', ["objId" => $request->id]);
    }

    public function show(Request $request)
    {
        return view('app.logistic.transaction.product-detail.show', ["objId" => $request->id]);
    }
}
