<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function core()
    {
        return view('app.core.setting.core.index');
    }

    public function logistic()
    {
        return view('app.core.setting.logistic.index');
    }
}
