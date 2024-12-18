<?php

use App\Http\Controllers\Logistic\Master\WarehouseController;
use App\Http\Controllers\Rsmh\Sakti\InterkoneksiSaktiCoaController;
use App\Http\Controllers\Rsmh\Sakti\InterkoneksiSaktiDetailBarangController;
use App\Http\Controllers\Rsmh\Sakti\InterkoneksiSaktiDetailCoaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Rsmh\Sakti\InterkoneksiSaktiKbkiController;
use App\Http\Controllers\Rsmh\Sakti\InterkoneksiSaktiSettingController;

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => InterkoneksiSaktiKbkiController::class, "prefix" => "interkoneksi_sakti_kbki", "as" => "interkoneksi_sakti_kbki."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });
    Route::group(["controller" => InterkoneksiSaktiCoaController::class, "prefix" => "interkoneksi_sakti_coa", "as" => "interkoneksi_sakti_coa."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });
    Route::group(["controller" => InterkoneksiSaktiDetailBarangController::class, "prefix" => "interkoneksi_sakti_detail_barang", "as" => "interkoneksi_sakti_detail_barang."], function () {
        Route::get('/', 'index')->name('index');

        Route::get('/warehouse/get', [WarehouseController::class, 'search'])->name('get.warehouse');
    });
    Route::group(["controller" => InterkoneksiSaktiDetailCoaController::class, "prefix" => "interkoneksi_sakti_detail_coa", "as" => "interkoneksi_sakti_detail_coa."], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group(["controller" => InterkoneksiSaktiSettingController::class, "prefix" => "interkoneksi_sakti_setting", "as" => "interkoneksi_sakti_setting."], function () {
        Route::get('/', 'index')->name('index');
    });
});
