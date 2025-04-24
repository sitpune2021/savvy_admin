<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\Driver\OrderController;
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
        'create', 'edit', 'destroy'
    ]);
    Route::get('products', [CustomerOrderController::class, 'products']);
    Route::get('contact_info', function() {
        $user = auth()->user()->load('customers');
        return response()->json([
            'status' => true,
            'message' => 'Contact info retrieved successfully',
            'data'=> [
                'id' => $user->id,
                'name'  => $user->contact_person,
                'phone_no' => $user->contact_person_phone,
                'address' => $user->shipping_address,
                'customer_id' => $user->customer_id,
                'customer_name' => optional($user->customers)->name,
            ]
            ]);
    });
    Route::post('logout', [AuthController::class, 'logout']);
});

// 🚚 Driver Routes
Route::middleware('auth:driver_api')->group(function () {
    // Orders
    Route::resource('orders', OrderController::class)->except([
        'create', 'store', 'edit', 'destroy'
    ]);
    Route::post('orders/update/{id}', [OrderController::class, 'update']);

    // Profile
    Route::resource('profile', ProfileController::class)->except([
        'create', 'edit', 'index', 'store'
    ]);

    // Maintenance
    Route::resource('maintenance', MaintenanceController::class)->except([
        'create', 'edit'
    ]);
});


