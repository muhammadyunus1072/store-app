<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;

class ImportDataController extends Controller
{
    public function index()
    {
        return view('app.core.import-data.index');
    }
}
