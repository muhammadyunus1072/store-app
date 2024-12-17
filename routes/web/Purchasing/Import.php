<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Purchasing\Import\ImportPurchaseOrderController;
use App\Http\Controllers\Purchasing\Master\SupplierController;

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => ImportPurchaseOrderController::class, "prefix" => "i_purchase_order", "as" => "i_purchase_order."], function () {
        Route::get('/', 'index')->name('index');

        Route::get('/supplier/get', [SupplierController::class, 'search'])->name('get.supplier');
    });
});
