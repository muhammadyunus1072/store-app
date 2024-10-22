<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group([], __DIR__ . '/web/Core/Core.php');
Route::group([], __DIR__ . '/web/Purchasing/Master.php');
Route::group([], __DIR__ . '/web/Purchasing/Transaction.php');
Route::group([], __DIR__ . '/web/Document/Master.php');
Route::group([], __DIR__ . '/web/Document/Transaction.php');
Route::group([], __DIR__ . '/web/Logistic/Master.php');
Route::group([], __DIR__ . '/web/Logistic/Transaction.php');
Route::group([], __DIR__ . '/web/Finance/Master.php');
