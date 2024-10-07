<?php

namespace App\Http\Controllers\Finance\Master;

use App\Http\Controllers\Controller;
use App\Repositories\Finance\Master\Tax\TaxRepository;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function index()
    {
        return view('app.finance.master.tax.index');
    }

    public function create()
    {
        return view('app.finance.master.tax.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.finance.master.tax.detail', ["objId" => $request->id]);
    }

    public function search(Request $request)
    {
        return TaxRepository::search($request->search);
    }
}
