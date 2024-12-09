<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Logistic\Master\WarehouseController;
use App\Http\Controllers\Logistic\Report\Warehouse\CurrentStockController;
use App\Http\Controllers\Logistic\Report\Warehouse\HistoryStockController;
use App\Http\Controllers\Logistic\Report\Warehouse\StockExpenseController;
use App\Http\Controllers\Logistic\Report\Warehouse\CurrentStockDetailController;
use App\Http\Controllers\Logistic\Report\Warehouse\HistoryStockDetailController;
use App\Http\Controllers\Logistic\Report\Warehouse\StockExpiredController;
use App\Http\Controllers\Logistic\Report\Warehouse\StockRequestInController;
use App\Http\Controllers\Logistic\Report\Warehouse\StockRequestOutController;

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => CurrentStockController::class, "prefix" => "r_current_stock_warehouse", "as" => "r_current_stock_warehouse."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => CurrentStockDetailController::class, "prefix" => "r_current_stock_detail_warehouse", "as" => "r_current_stock_detail_warehouse."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => HistoryStockController::class, "prefix" => "r_history_stock_warehouse", "as" => "r_history_stock_warehouse."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => HistoryStockDetailController::class, "prefix" => "r_history_stock_detail_warehouse", "as" => "r_history_stock_detail_warehouse."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => StockExpenseController::class, "prefix" => "r_stock_expense_warehouse", "as" => "r_stock_expense_warehouse."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => StockExpiredController::class, "prefix" => "r_stock_expired_warehouse", "as" => "r_stock_expired_warehouse."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => StockRequestInController::class, "prefix" => "r_stock_request_in_warehouse", "as" => "r_stock_request_in_warehouse."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => StockRequestOutController::class, "prefix" => "r_stock_request_out_warehouse", "as" => "r_stock_request_out_warehouse."], function () {
        Route::get('/', 'index')->name('index');
    });
});
