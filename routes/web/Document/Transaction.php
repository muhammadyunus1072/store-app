<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Document\Transaction\ApprovalController;


Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => ApprovalController::class, "prefix" => "approval", "as" => "approval."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('{id}/show', 'show')->name('show');
    });
});
