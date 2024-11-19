<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Logistic\Master\ProductController;
use App\Http\Controllers\Logistic\Master\CategoryProductController;

Route::middleware(['auth'])->group(function () {
    Route::group(["prefix" => "logistic/filter", "as" => "logistic.filter."], function () {
        Route::get('product/get', [ProductController::class, 'search'])->name('find.product');
        Route::get('category_product/get', [CategoryProductController::class, 'search'])->name('find.category_product');
    });
});
