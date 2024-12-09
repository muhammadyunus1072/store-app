<?php

namespace App\Http\Controllers\Finance\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaxEditorController extends Controller
{
    public function index()
    {
        return view('app.finance.master.tax-editor.index');
    }
}
