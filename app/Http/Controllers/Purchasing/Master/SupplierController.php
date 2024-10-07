<?php

namespace App\Http\Controllers\Purchasing\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Purchasing\Master\Supplier\SupplierRepository;

class SupplierController extends Controller
{
    public function index()
    {
        return view('app.purchasing.master.supplier.index');
    }

    public function create()
    {
        return view('app.purchasing.master.supplier.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.purchasing.master.supplier.detail', ["objId" => $request->id]);
    }
    // Select2
    // Supplier
    public function search(Request $request)
    {
        return SupplierRepository::search($request->search);
    }
}
