<?php

namespace App\Http\Controllers\Logistic\Import;

use App\Http\Controllers\Controller;

class ImportMasterDataController extends Controller
{
    public function index()
    {
        return view('app.logistic.import.master-data.index');
    }
}
