<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Logistic\Master\ProductController;
use App\Http\Controllers\Purchasing\Master\SupplierController;
use App\Http\Controllers\Purchasing\Transaction\PurchaseOrderController;

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => PurchaseOrderController::class, "prefix" => "purchase_order", "as" => "purchase_order."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');

        Route::get('/product/get', [ProductController::class, 'search'])->name('get.product');
        Route::get('/supplier/get', [SupplierController::class, 'search'])->name('get.supplier');
    });
});
