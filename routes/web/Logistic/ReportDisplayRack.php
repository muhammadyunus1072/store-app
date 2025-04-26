<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Logistic\Report\DisplayRack\CurrentStockController;

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => CurrentStockController::class, "prefix" => "r_current_stock_display_rack", "as" => "r_current_stock_display_rack."], function () {
        Route::get('/', 'index')->name('index');
    });
});
