<?php

namespace App\Http\Controllers\Rsmh\Sakti;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InterkoneksiSaktiDetailBarangController extends Controller
{
    public function index()
    {
        return view('app.rsmh.sakti.interkoneksi-sakti-detail-barang.index');
    }
}
