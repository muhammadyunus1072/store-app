<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Logistic\Master\ProductController;
use App\Http\Controllers\Logistic\Master\WarehouseController;
use App\Http\Controllers\Logistic\Report\CurrentStockController;
use App\Http\Controllers\Logistic\Report\HistoryStockController;
use App\Http\Controllers\Logistic\Report\StockExpenseController;
use App\Http\Controllers\Logistic\Report\ExpenseReportController;
use App\Http\Controllers\Logistic\Master\CategoryProductController;
use App\Http\Controllers\Logistic\Report\CurrentStockDetailController;
use App\Http\Controllers\Logistic\Report\HistoryStockDetailController;
use App\Http\Controllers\Logistic\Report\CurrentStockWarehouseController;
use App\Http\Controllers\Logistic\Report\HistoryStockWarehouseController;
use App\Http\Controllers\Logistic\Report\StockExpenseWarehouseController;
use App\Http\Controllers\Logistic\Report\WarehouseExpenseReportController;
use App\Http\Controllers\Logistic\Report\CurrentStockDetailWarehouseController;
use App\Http\Controllers\Logistic\Report\HistoryStockDetailWarehouseController;

Route::get('/product/get', [ProductController::class, 'search'])->name('find.product');
Route::get('/category_product/get', [CategoryProductController::class, 'search'])->name('find.category_product');
Route::get('/warehouse/get', [WarehouseController::class, 'search'])->name('find.warehouse');

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => CurrentStockController::class, "prefix" => "current_stock", "as" => "current_stock."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => CurrentStockDetailController::class, "prefix" => "current_stock_detail", "as" => "current_stock_detail."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => CurrentStockWarehouseController::class, "prefix" => "current_stock_warehouse", "as" => "current_stock_warehouse."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => CurrentStockDetailWarehouseController::class, "prefix" => "current_stock_detail_warehouse", "as" => "current_stock_detail_warehouse."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => HistoryStockController::class, "prefix" => "history_stock", "as" => "history_stock."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => HistoryStockDetailController::class, "prefix" => "history_stock_detail", "as" => "history_stock_detail."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => HistoryStockWarehouseController::class, "prefix" => "history_stock_warehouse", "as" => "history_stock_warehouse."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => HistoryStockDetailWarehouseController::class, "prefix" => "history_stock_detail_warehouse", "as" => "history_stock_detail_warehouse."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => StockExpenseController::class, "prefix" => "stock_expense_report", "as" => "stock_expense_report."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => StockExpenseWarehouseController::class, "prefix" => "stock_expense_warehouse", "as" => "stock_expense_warehouse."], function () {
        Route::get('/', 'index')->name('index');
    });
});
