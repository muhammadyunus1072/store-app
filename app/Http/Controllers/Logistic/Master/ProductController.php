<?php

namespace App\Http\Controllers\Logistic\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Logistic\Master\Product\ProductRepository;

class ProductController extends Controller
{
    public function index()
    {
        return view('app.logistic.master.product.index');
    }

    public function create()
    {
        return view('app.logistic.master.product.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.logistic.master.product.detail', ["objId" => $request->id]);
    }

    public function show(Request $request)
    {
        return view('app.logistic.master.product.show', ["objId" => $request->id]);
    }

    // Select2
    // Product
    public function search(Request $request)
    {
        return ProductRepository::search($request->search);
    }
}
