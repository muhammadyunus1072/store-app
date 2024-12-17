<?php

namespace App\Http\Controllers\Rsmh\Sakti;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InterkoneksiSaktiCoaController extends Controller
{
    public function index()
    {
        return view('app.rsmh.sakti.interkoneksi-sakti-coa.index');
    }

    public function create()
    {
        return view('app.rsmh.sakti.interkoneksi-sakti-coa.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.rsmh.sakti.interkoneksi-sakti-coa.detail', ["objId" => $request->id]);
    }
}
