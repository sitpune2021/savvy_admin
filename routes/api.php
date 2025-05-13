<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\Driver\OrderController as DriverOrderController;
use App\Http\Controllers\API\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\API\Driver\ProfileController;
use App\Http\Controllers\API\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\API\Driver\MaintenanceController;

// Driver 🔐 Auth RoutesA
Route::post('send-otp', [AuthController::class, 'sendOtp']); 
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);

 // Customer and Vendor 🔐 Auth Routes
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('verify-account', [AuthController::class, 'verifyAccount']); 
Route::post('reset-password', [AuthController::class, 'resetPassword']); 

// 👤 Customer Routes
Route::middleware('auth:customer_api')->prefix('customer')->group(function () {
    Route::resource('orders', CustomerOrderController::class)->except([
         'edit', 'destroy', 'update', 'show'
    ]);
    Route::get('orders/send-requests', [CustomerOrderController::class, 'getRequestedOrders']);
    Route::get('products', [CustomerOrderController::class, 'products']);
    Route::post('password', [CustomerProfileController::class, 'updatePassword']);
    Route::post('delete-account', [CustomerProfileController::class, 'deleteAccount']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('request-order-list', [CustomerOrderController::class, 'requestOrderList']);
    Route::get('accept-order-request/{id}', [CustomerOrderController::class, 'requestOrderUpdate']);
});

// 🚚 Driver Routes
Route::middleware('auth:driver_api')->group(function () {
    Route::resource('order', DriverOrderController::class)->except([
        'create', 'store', 'edit', 'destroy'
    ]);
    Route::post('order_update/{id}', [DriverOrderController::class, 'update']);
    Route::resource('profile', ProfileController::class)->except([
        'create', 'edit', 'store','show'
    ]);
    Route::resource('maintenance', MaintenanceController::class)->except([
        'create', 'edit', 'destroy', 'update', 'show'
    ]);
    Route::post('logout', [AuthController::class, 'logout']);
});


