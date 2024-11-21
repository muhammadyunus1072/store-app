<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Logistic\Report\All\CurrentStockController;
use App\Http\Controllers\Logistic\Report\All\HistoryStockController;
use App\Http\Controllers\Logistic\Report\All\StockExpenseController;
use App\Http\Controllers\Logistic\Report\All\CurrentStockDetailController;
use App\Http\Controllers\Logistic\Report\All\HistoryStockDetailController;
use App\Http\Controllers\Logistic\Report\All\StockExpiredController;

Route::middleware(['auth', 'access_permission'])->group(function () {
    /*
    | REPORT ALL
    */
    Route::group(["controller" => CurrentStockController::class, "prefix" => "r_current_stock", "as" => "r_current_stock."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => CurrentStockDetailController::class, "prefix" => "r_current_stock_detail", "as" => "r_current_stock_detail."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => HistoryStockController::class, "prefix" => "r_history_stock", "as" => "r_history_stock."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => HistoryStockDetailController::class, "prefix" => "r_history_stock_detail", "as" => "r_history_stock_detail."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => StockExpenseController::class, "prefix" => "r_stock_expense", "as" => "r_stock_expense."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => StockExpiredController::class, "prefix" => "r_stock_expired", "as" => "r_stock_expired."], function () {
        Route::get('/', 'index')->name('index');
    });
});
