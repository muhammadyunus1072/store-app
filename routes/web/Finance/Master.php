<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\Master\TaxController;
use App\Http\Controllers\Finance\Master\TaxEditorController;

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => TaxController::class, "prefix" => "tax", "as" => "tax."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });
    Route::group(["controller" => TaxEditorController::class, "prefix" => "tax_editor", "as" => "tax_editor."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });
});
