<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\Driver\OrderController;
use App\Http\Controllers\API\Driver\ProfileController;
use App\Http\Controllers\API\Driver\MaintenanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::post('login', [App\Http\Controllers\API\AuthController::class, 'login'])->name('login');
// Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('send-otp', [AuthController::class, 'sendOtp']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);

Route::middleware('auth:driver_api')->group(function () {
    Route::resource('order', OrderController::class)->except([
        'create','store', 'edit', 'destroy'
    ]);
    Route::post('order_card/{id}', [OrderController::class, 'updateCard']);
    Route::resource('profile', ProfileController::class)->except([
        'create', 'edit', 'index', 'store'
    ]);
    Route::resource('maintenance', MaintenanceController::class)->except([
        'create', 'edit',
    ]);

});
