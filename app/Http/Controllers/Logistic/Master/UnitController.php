<?php

namespace App\Http\Controllers\Logistic\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Logistic\Master\Unit\UnitRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;

class UnitController extends Controller
{
    public function index()
    {
        return view('app.logistic.master.unit.index');
    }

    public function create()
    {
        return view('app.logistic.master.unit.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.logistic.master.unit.detail', ["objId" => $request->id]);
    }

    public function show(Request $request)
    {
        return view('app.logistic.master.unit.show', ["objId" => $request->id]);
    }
    // Select2
    // Unit
    public function search(Request $request)
    {
        return UnitRepository::search($request->search);
    }
}
