<?php

use App\Http\Controllers\Core\CompanyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Logistic\Master\ProductController;
use App\Http\Controllers\Logistic\Master\WarehouseController;
use App\Http\Controllers\Logistic\Transaction\StockExpenseController;
use App\Http\Controllers\Logistic\Transaction\StockOpnameController;
use App\Http\Controllers\Logistic\Transaction\StockRequestController;

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => StockRequestController::class, "prefix" => "stock_request", "as" => "stock_request."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
        Route::get('{id}/show', 'show')->name('show');

        Route::get('/product/get', [ProductController::class, 'search'])->name('get.product');
        Route::get('/warehouse/get', [WarehouseController::class, 'search'])->name('get.warehouse');
        Route::get('/company/get', [CompanyController::class, 'search'])->name('get.company');
    });

    Route::group(["controller" => StockExpenseController::class, "prefix" => "stock_expense", "as" => "stock_expense."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
        Route::get('{id}/show', 'show')->name('show');

        Route::get('/product/get', [ProductController::class, 'search'])->name('get.product');
    });

    Route::group(["controller" => StockOpnameController::class, "prefix" => "stock_opname", "as" => "stock_opname."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
        Route::get('{id}/show', 'show')->name('show');

        Route::get('/product/get', [ProductController::class, 'search'])->name('get.product');
    });
});
