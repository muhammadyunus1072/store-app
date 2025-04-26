<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Logistic\Master\ProductController;
use App\Http\Controllers\Sales\Transaction\CashierTransactionController;

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => CashierTransactionController::class, "prefix" => "cashier_transaction", "as" => "cashier_transaction."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');

        Route::get('/product/get', [ProductController::class, 'search'])->name('get.product');
    });
});
