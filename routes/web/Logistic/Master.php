<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Logistic\Master\UnitController;
use App\Http\Controllers\Logistic\Master\ProductController;
use App\Http\Controllers\Logistic\Master\WarehouseController;
use App\Http\Controllers\Logistic\Master\CategoryProductController;


Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => UnitController::class, "prefix" => "unit", "as" => "unit."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });
    Route::group(["controller" => CategoryProductController::class, "prefix" => "category_product", "as" => "category_product."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });
    Route::group(["controller" => ProductController::class, "prefix" => "product", "as" => "product."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');

        Route::get('/category_product/get', [CategoryProductController::class, 'search'])->name('get.category_product');
        Route::get('/unit/get', [UnitController::class, 'search'])->name('get.unit');
    });
    Route::group(["controller" => WarehouseController::class, "prefix" => "warehouse", "as" => "warehouse."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });
});
