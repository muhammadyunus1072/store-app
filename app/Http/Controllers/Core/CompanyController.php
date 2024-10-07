<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Core\Company\CompanyRepository;

class CompanyController extends Controller
{
    public function index()
    {
        return view('app.core.company.index');
    }

    public function create()
    {
        return view('app.core.company.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.core.company.detail', ["objId" => $request->id]);
    }
    // Select2
    // Company
    public function search(Request $request)
    {
        return CompanyRepository::search($request);
    }
}
