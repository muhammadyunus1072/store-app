<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Logistic\Master\ProductController;
use App\Http\Controllers\Logistic\Master\WarehouseController;
use App\Http\Controllers\Logistic\Master\CategoryProductController;
use App\Http\Controllers\Logistic\Report\StockCardReportController;
use App\Http\Controllers\Logistic\Report\CurrentStockReportController;
use App\Http\Controllers\Logistic\Report\StockCardDetailReportController;
use App\Http\Controllers\Logistic\Report\CurrentStockDetailReportController;
use App\Http\Controllers\Logistic\Report\StockCardWarehouseReportController;
use App\Http\Controllers\Logistic\Report\CurrentStockWarehouseReportController;
use App\Http\Controllers\Logistic\Report\StockCardWarehouseDetailReportController;
use App\Http\Controllers\Logistic\Report\CurrentStockWarehouseDetailReportController;

Route::get('/product/get', [ProductController::class, 'search'])->name('find.product');
Route::get('/category_product/get', [CategoryProductController::class, 'search'])->name('find.category_product');
Route::get('/warehouse/get', [WarehouseController::class, 'search'])->name('find.warehouse');

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => CurrentStockReportController::class, "prefix" => "current_stock", "as" => "current_stock."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => CurrentStockDetailReportController::class, "prefix" => "current_stock_detail", "as" => "current_stock_detail."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => CurrentStockWarehouseReportController::class, "prefix" => "current_stock_warehouse", "as" => "current_stock_warehouse."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => CurrentStockWarehouseDetailReportController::class, "prefix" => "current_stock_warehouse_detail", "as" => "current_stock_warehouse_detail."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => StockCardReportController::class, "prefix" => "card_stock", "as" => "card_stock."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => StockCardDetailReportController::class, "prefix" => "card_stock_detail", "as" => "card_stock_detail."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => StockCardWarehouseReportController::class, "prefix" => "card_stock_warehouse", "as" => "card_stock_warehouse."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => StockCardWarehouseDetailReportController::class, "prefix" => "card_stock_warehouse_detail", "as" => "card_stock_warehouse_detail."], function () {
        Route::get('/', 'index')->name('index');
    });
});
