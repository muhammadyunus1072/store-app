<?php

namespace App\Http\Controllers\Logistic\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;

class CategoryProductController extends Controller
{
    public function index()
    {
        return view('app.logistic.master.category-product.index');
    }

    public function create()
    {
        return view('app.logistic.master.category-product.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.logistic.master.category-product.detail', ["objId" => $request->id]);
    }

    public function show(Request $request)
    {
        return view('app.logistic.master.category-product.show', ["objId" => $request->id]);
    }
    // Select2
    // CategoryProduct
    public function search(Request $request)
    {
        return CategoryProductRepository::search($request->search);
    }
}
