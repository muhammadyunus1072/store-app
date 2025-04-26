<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sales\Master\PaymentMethodController;

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => PaymentMethodController::class, "prefix" => "payment_method", "as" => "payment_method."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });
});
