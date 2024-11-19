<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Logistic\Import\ImportDataController;


Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => ImportDataController::class, "prefix" => "import_data_logistic", "as" => "import_data_logistic."], function () {
        Route::get('/', 'index')->name('index');
    });
});
