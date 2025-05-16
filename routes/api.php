<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\CustomController;
use App\Http\Controllers\API\DriverController;
use App\Http\Controllers\API\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\API\Driver\ProfileController;
use App\Http\Controllers\API\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\API\Driver\MaintenanceController;
use Illuminate\Http\Request; 

// Driver 🔐 Auth Routes
Route::post('send-otp', [AuthController::class, 'sendOtp']); 
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);

 // Customer and Vendor 🔐 Auth Routes
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('verify-account', [AuthController::class, 'verifyAccount']); 
Route::post('reset-password', [AuthController::class, 'resetPassword']); 

Route::middleware('auth:sanctum')->group(function () {
     Route::post('password', [CustomerProfileController::class, 'updatePassword']);
     Route::post('logout', [AuthController::class, 'logout']);
     Route::post('delete-account', [CustomerProfileController::class, 'deleteAccount']);
     Route::resource('order', OrderController::class)->except([
        'create', 'store', 'edit', 'destroy', 'update'
    ]);
    Route::resource('driver', DriverController::class);
    Route::get('plant', [CustomController::class, 'plants']);
    Route::get('routes/{plantId}', [CustomController::class, 'getRoutesByPlant']);
    Route::get('drivers/{routeId}', [CustomController::class, 'getDriversByRoute']);
    Route::get('shipping-address', [CustomController::class, 'getShipingAddress']);

});

// 👤 Customer Routes
Route::middleware('auth:customer_api')->prefix('customer')->group(function () {
    Route::resource('orders', CustomerOrderController::class)->except([
         'edit', 'destroy', 'update', 'show'
    ]);
    Route::get('orders/send-requests', [CustomerOrderController::class, 'getRequestedOrders']);
    Route::get('products', [CustomerOrderController::class, 'products']);
    Route::get('request-order-list', [CustomerOrderController::class, 'requestOrderList']);
    Route::get('accept-order-request/{id}', [CustomerOrderController::class, 'requestOrderUpdate']);
});

// 🚚 Driver Routes
Route::middleware('auth:driver_api')->group(function () {
    Route::post('order_update/{id}', [OrderController::class, 'update']);
    Route::resource('profile', DriverController::class)->except([
        'create', 'edit', 'store','index'
    ]);
    Route::resource('maintenance', MaintenanceController::class)->except([
        'create', 'edit', 'destroy', 'update', 'show'
    ]);
});




