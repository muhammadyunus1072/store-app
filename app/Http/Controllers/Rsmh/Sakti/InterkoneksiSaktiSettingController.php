<?php

namespace App\Http\Controllers\Rsmh\Sakti;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InterkoneksiSaktiSettingController extends Controller
{
    public function index()
    {
        return view('app.rsmh.sakti.interkoneksi-sakti-setting.index');
    }
}
