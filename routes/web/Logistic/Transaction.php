<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Logistic\Master\ProductController;
use App\Http\Controllers\Logistic\Transaction\StockExpenseController;
use App\Http\Controllers\Logistic\Transaction\StockRequestController;

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => StockRequestController::class, "prefix" => "stock_request", "as" => "stock_request."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');

        Route::get('/product/get', [ProductController::class, 'search'])->name('get.product');
    });
    
    Route::group(["controller" => StockExpenseController::class, "prefix" => "stock_expense", "as" => "stock_expense."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');

        Route::get('/product/get', [ProductController::class, 'search'])->name('get.product');
    });
});
