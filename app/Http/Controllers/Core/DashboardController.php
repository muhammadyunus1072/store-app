<?php

namespace App\Http\Controllers\Core;

use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        return view('app.layouts.panel');
    }

}
