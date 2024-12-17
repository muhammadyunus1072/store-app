<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Logistic\Master\WarehouseController;
use App\Http\Controllers\Logistic\Import\ImportMasterDataController;
use App\Http\Controllers\Logistic\Import\ImportStockExpenseController;
use App\Http\Controllers\Logistic\Import\ImportStockRequestController;

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => ImportMasterDataController::class, "prefix" => "i_master_data_logistic", "as" => "i_master_data_logistic."], function () {
        Route::get('/', 'index')->name('index');
    });

    Route::group(["controller" => ImportStockExpenseController::class, "prefix" => "i_stock_expense", "as" => "i_stock_expense."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('/warehouse/get', [WarehouseController::class, 'search'])->name('get.warehouse');
    });

    Route::group(["controller" => ImportStockRequestController::class, "prefix" => "i_stock_request", "as" => "i_stock_request."], function () {
        Route::get('/', 'index')->name('index');

        Route::get('/warehouse/get', [WarehouseController::class, 'search'])->name('get.warehouse');
    });
});
