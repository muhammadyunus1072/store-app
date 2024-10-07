<?php

namespace App\Http\Controllers\Logistic\Transaction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GoodReceiveController extends Controller
{
    public function index()
    {
        return view('app.logistic.transaction.good-receive.index');
    }

    public function create()
    {
        return view('app.logistic.transaction.good-receive.detail', ["objId" => null]);
    }

    public function edit(Request $request)
    {
        return view('app.logistic.transaction.good-receive.detail', ["objId" => $request->id]);
    }

    public function show(Request $request)
    {
        return view('app.logistic.transaction.good-receive.show', ["objId" => $request->id]);
    }
}
