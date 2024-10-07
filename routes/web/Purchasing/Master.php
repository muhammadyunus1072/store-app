<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Purchasing\Master\SupplierController;
use App\Http\Controllers\Purchasing\Master\CategorySupplierController;


Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => CategorySupplierController::class, "prefix" => "category_supplier", "as" => "category_supplier."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });

    Route::group(["controller" => SupplierController::class, "prefix" => "supplier", "as" => "supplier."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');

        Route::get('/category_supplier/get', [CategorySupplierController::class, 'search'])->name('get.category_supplier');
    });
});
