<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Logistic\Master\ProductController;
use App\Http\Controllers\Logistic\Master\CategoryProductController;
use App\Http\Controllers\Purchasing\Master\SupplierController;

Route::middleware(['auth'])->group(function () {
    Route::group(["prefix" => "purchasing/filter", "as" => "purchasing.filter."], function () {
        Route::get('product/get', [ProductController::class, 'search'])->name('find.product');
        Route::get('category_product/get', [CategoryProductController::class, 'search'])->name('find.category_product');
        Route::get('supplier/get', [SupplierController::class, 'search'])->name('find.supplier');
    });
});
