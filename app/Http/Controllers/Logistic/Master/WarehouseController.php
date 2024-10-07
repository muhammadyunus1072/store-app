<?php

namespace App\Http\Controllers\Logistic\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;

class WarehouseController extends Controller
{
    public function index()
    {
        return view('app.logistic.master.warehouse.index');
    }

    public function create()
    {
        return view('app.logistic.master.warehouse.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.logistic.master.warehouse.detail', ["objId" => $request->id]);
    }

    public function show(Request $request)
    {
        return view('app.logistic.master.warehouse.show', ["objId" => $request->id]);
    }

    public function search(Request $request)
    {
        return WarehouseRepository::search($request);
    }
}
