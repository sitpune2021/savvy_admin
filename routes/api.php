<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\Driver\OrderController as DriverOrderController;
use App\Http\Controllers\API\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\API\Driver\ProfileController;
use App\Http\Controllers\API\Driver\MaintenanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are assigned to the "api" middleware group.
|--------------------------------------------------------------------------
*/

// 🔐 Auth Routes
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('send-otp', [AuthController::class, 'sendOtp']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('verify-account', [AuthController::class, 'verifyAccount']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:driver_api');

// 👤 Customer Routes
Route::middleware('auth:customer_api')->prefix('customer')->group(function () {
    Route::resource('orders', CustomerOrderController::class)->except([
        'create', 'store', 'edit', 'destroy'
    ]);
});

// 🚚 Driver Routes
Route::middleware('auth:driver_api')->group(function () {
    // Orders
    Route::resource('orders', DriverOrderController::class)->except([
        'create', 'store', 'edit', 'destroy'
    ]);
    Route::post('orders/update/{id}', [DriverOrderController::class, 'update']);

    // Profile
    Route::resource('profile', ProfileController::class)->except([
        'create', 'edit', 'index', 'store'
    ]);

    // Maintenance
    Route::resource('maintenance', MaintenanceController::class)->except([
        'create', 'edit'
    ]);
});
