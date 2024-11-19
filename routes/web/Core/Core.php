<?php

use App\Http\Controllers\Core\AuthController;
use App\Http\Controllers\Core\CaptchaController;
use App\Http\Controllers\Core\UserController;
use App\Http\Controllers\Core\CompanyController;
use App\Http\Controllers\Core\MenuNotificationController;
use App\Http\Controllers\Core\PermissionController;
use App\Http\Controllers\Core\RoleController;
use App\Http\Controllers\Core\DashboardController;
use App\Http\Controllers\Core\SettingController;

use App\Http\Controllers\Core\ImportDataController;

use App\Http\Controllers\Logistic\Master\WarehouseController;
use App\Http\Controllers\Finance\Master\TaxController;

use Illuminate\Support\Facades\Route;

Route::get('/reload-captcha', [CaptchaController::class, 'reload'])->name('reload_captcha');

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'login']);
    Route::get("/login", [AuthController::class, "login"])->name("login");
    Route::get("/logout", [AuthController::class, "logout"])->name('logout');
    Route::get("/register", [AuthController::class, "register"])->name("register");
    Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.request');
    Route::get('/reset-password/{token}',  [AuthController::class, 'resetPassword'])->name('password.reset');
    Route::get("/email_verification", [AuthController::class, "emailVerification"])->name("verification.index");
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, "emailVerificationVerify"])->middleware('signed')->name('verification.verify');
});

Route::middleware('auth')->group(function () {
    Route::get("/logout", [AuthController::class, "logout"])->name('logout');
    Route::get('/profile', [AuthController::class, "profile"])->name('profile');

    Route::group(["controller" => DashboardController::class, "prefix" => "dashboard", "as" => "dashboard."], function () {
        Route::get('/', 'index')->name('index');
    });

    Route::get("/menu-notification", [MenuNotificationController::class, "index"])->name('menu_notification');
});

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => UserController::class, "prefix" => "user", "as" => "user."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');

        Route::get('/company/get', [CompanyController::class, 'search'])->name('get.company');
        Route::get('/warehouse/get', [WarehouseController::class, 'search'])->name('get.warehouse');
    });

    Route::group(["controller" => CompanyController::class, "prefix" => "company", "as" => "company."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');

        Route::get('/warehouse/get', [WarehouseController::class, 'search'])->name('get.warehouse');
    });

    Route::group(["controller" => RoleController::class, "prefix" => "role", "as" => "role."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });

    Route::group(["controller" => PermissionController::class, "prefix" => "permission", "as" => "permission."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });

    Route::group(["controller" => SettingController::class], function () {
        // Core
        Route::get('/setting_core', 'core')->name('setting_core.index');

        // Logistic 
        Route::get('/setting_logistic', 'logistic')->name('setting_logistic.index');

        // Purchasing 
        Route::get('/setting_purchasing', 'purchasing')->name('setting_purchasing.index');
        Route::get('/setting_purchasing/tax/get', [TaxController::class, 'search'])->name('setting_purchasing.get.tax');
    });

    Route::group(["controller" => ImportDataController::class, "prefix" => "import_data_logistic", "as" => "import_data_logistic."], function () {
        Route::get('/', 'index')->name('index');
    });
});
