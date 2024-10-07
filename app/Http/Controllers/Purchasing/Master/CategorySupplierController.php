<?php

namespace App\Http\Controllers\Purchasing\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Purchasing\Master\CategorySupplier\CategorySupplierRepository;

class CategorySupplierController extends Controller
{
    public function index()
    {
        return view('app.purchasing.master.category-supplier.index');
    }

    public function create()
    {
        return view('app.purchasing.master.category-supplier.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.purchasing.master.category-supplier.detail', ["objId" => $request->id]);
    }
    // Select2
    // CategorySupplier
    public function search(Request $request)
    {
        return CategorySupplierRepository::search($request->search);
    }
}
