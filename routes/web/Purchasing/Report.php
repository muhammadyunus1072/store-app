<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Logistic\Master\ProductController;
use App\Http\Controllers\Logistic\Master\WarehouseController;
use App\Http\Controllers\Purchasing\Master\SupplierController;
use App\Http\Controllers\Logistic\Master\CategoryProductController;
use App\Http\Controllers\Purchasing\Report\PurchaseOrderController;
use App\Http\Controllers\Purchasing\Report\PurchaseOrderProductController;
use App\Http\Controllers\Purchasing\Report\PurchaseOrderProductDetailController;

Route::get('/product/get', [ProductController::class, 'search'])->name('find.product');
Route::get('/category_product/get', [CategoryProductController::class, 'search'])->name('find.category_product');
Route::get('/warehouse/get', [WarehouseController::class, 'search'])->name('find.warehouse');
Route::get('/supplier/get', [SupplierController::class, 'search'])->name('find.supplier');

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => PurchaseOrderController::class, "prefix" => "purchase_order_report", "as" => "purchase_order_report."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => PurchaseOrderProductController::class, "prefix" => "purchase_order_product_report", "as" => "purchase_order_product_report."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => PurchaseOrderProductDetailController::class, "prefix" => "purchase_order_product_detail_report", "as" => "purchase_order_product_detail_report."], function () {
        Route::get('/', 'index')->name('index');
    });
});
