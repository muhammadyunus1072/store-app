<?php

namespace App\Http\Controllers\Rsmh\Sakti;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InterkoneksiSaktiKbkiController extends Controller
{
    public function index()
    {
        return view('app.rsmh.sakti.interkoneksi-sakti-kbki.index');
    }

    public function create()
    {
        return view('app.rsmh.sakti.interkoneksi-sakti-kbki.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.rsmh.sakti.interkoneksi-sakti-kbki.detail', ["objId" => $request->id]);
    }
}
