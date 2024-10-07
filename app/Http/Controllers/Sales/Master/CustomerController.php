<?php

namespace App\Http\Controllers\Sales\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return view('app.sales.master.customer.index');
    }

    public function create()
    {
        return view('app.sales.master.customer.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.sales.master.customer.detail', ["objId" => $request->id]);
    }
}
