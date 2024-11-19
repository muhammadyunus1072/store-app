<?php

namespace App\Http\Controllers\Logistic\Import;

use App\Http\Controllers\Controller;

class ImportDataController extends Controller
{
    public function index()
    {
        return view('app.logistic.import.import-data.index');
    }
}
