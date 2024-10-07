<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Core\CompanyController;
use App\Http\Controllers\Logistic\Master\ProductController;
use App\Http\Controllers\Logistic\Master\WarehouseController;
use App\Http\Controllers\Logistic\Transaction\GoodReceiveController;
use App\Http\Controllers\Logistic\Transaction\StockExpenseController;
use App\Http\Controllers\Logistic\Transaction\StockRequestController;
use App\Http\Controllers\Purchasing\Master\SupplierController;
use App\Http\Controllers\Purchasing\Transaction\PurchaseOrderController;


Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => GoodReceiveController::class, "prefix" => "good_receive", "as" => "good_receive."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');

        Route::get('/product/get', [ProductController::class, 'search'])->name('get.product');
        Route::get('/supplier/get', [SupplierController::class, 'search'])->name('get.supplier');
        Route::get('/warehouse/get', [WarehouseController::class, 'search'])->name('get.warehouse');
        Route::get('/company/get', [CompanyController::class, 'search'])->name('get.company');
        Route::get('/purchase_order/get', [PurchaseOrderController::class, 'search'])->name('get.purchase_order');
    });

    Route::group(["controller" => StockRequestController::class, "prefix" => "stock_request", "as" => "stock_request."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');

        Route::get('/product/get', [ProductController::class, 'search'])->name('get.product');
        Route::get('/warehouse/get', [WarehouseController::class, 'search'])->name('get.warehouse');
    });
    
    Route::group(["controller" => StockExpenseController::class, "prefix" => "stock_expense", "as" => "stock_expense."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');

        Route::get('/product/get', [ProductController::class, 'search'])->name('get.product');
        Route::get('/warehouse/get', [WarehouseController::class, 'search'])->name('get.warehouse');
        Route::get('/company/get', [CompanyController::class, 'search'])->name('get.company');
    });
});
