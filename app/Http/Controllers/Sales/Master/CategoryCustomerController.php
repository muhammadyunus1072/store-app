<?php

namespace App\Http\Controllers\Sales\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Sales\Master\CategoryCustomer\CategoryCustomerRepository;

class CategoryCustomerController extends Controller
{
    public function index()
    {
        return view('app.sales.master.category-customer.index');
    }

    public function create()
    {
        return view('app.sales.master.category-customer.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.sales.master.category-customer.detail', ["objId" => $request->id]);
    }
    // Select2
    // CategoryCustomer
    public function search(Request $request)
    {
        return CategoryCustomerRepository::search($request->search);
    }
}
