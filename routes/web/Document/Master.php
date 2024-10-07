<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Core\UserController;
use App\Http\Controllers\Document\Master\ApprovalConfigController;
use App\Http\Controllers\Document\Master\StatusApprovalController;


Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => ApprovalConfigController::class, "prefix" => "approval_config", "as" => "approval_config."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
        
        Route::get('/user/get', [UserController::class, 'search'])->name('get.user');
    });
    Route::group(["controller" => StatusApprovalController::class, "prefix" => "status_approval", "as" => "status_approval."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });
});
