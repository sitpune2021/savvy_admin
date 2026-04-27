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
use App\Http\Controllers\API\StockProductionController;
use App\Http\Controllers\API\DistributorOrderController;
use App\Http\Controllers\API\DistributorController;
use App\Http\Controllers\API\DistributorProductionController;
use Illuminate\Http\Request; 

// Driver 🔐 Auth Routes
Route::post('send-otp', [AuthController::class, 'sendOtp']); 
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);

 // Customer and Vendor and  🔐 Auth Routes
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('verify-account', [AuthController::class, 'verifyAccount']); 
Route::post('reset-password', [AuthController::class, 'resetPassword']); 
Route::get('app-versions', [AuthController::class, 'appVersion']); 

Route::middleware('auth:sanctum')->group(function () {
    Route::post('password', [CustomerProfileController::class, 'updatePassword']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('delete-account', [CustomerProfileController::class, 'deleteAccount']);
    Route::resource('order', OrderController::class)->except([
        'create', 'store', 'edit', 'destroy', 'update'
        ]);
    Route::resource('distributors', DistributorController::class);
    Route::post('distributor-orders/{id}/action', [DistributorOrderController::class, 'action']);
    Route::post('distributor-orders/{id}/produce', [DistributorOrderController::class, 'produce']);
    Route::post('distributor-orders/{id}/dispatch', [DistributorOrderController::class, 'dispatch']);
    Route::resource('distributor-orders', DistributorOrderController::class);
    Route::resource('driver', DriverController::class);

    Route::get('plant', [CustomController::class, 'plants']);
    Route::get('routes/{plantId}', [CustomController::class, 'getRoutesByPlant']);
    Route::get('drivers/{routeId}', [CustomController::class, 'getDriversByRoute']);
    Route::get('shipping-address', [CustomController::class, 'getShipingAddress']);
    Route::post('shipping-address/{id}', [CustomController::class, 'updateShippingAddressForVendor']);
    Route::get('reasons', [CustomController::class, 'getReasons']);
    Route::get('digital-card', [CustomController::class, 'getDigitalCard']);
    Route::get('order-request', [CustomController::class, 'getOrderRequest']);
    Route::get('raw-stock', [CustomController::class, 'getRawStock']);
    Route::get('raw-stock-list', [CustomController::class, 'getNewStockList']);

    Route::post('accept-stock/{id}', [CustomController::class, 'acceptStock']);
    Route::get('labels', [CustomController::class, 'getLabels']);
    Route::get('jar-maintenance', [CustomController::class, 'getJarMaintenanceList']);
    Route::post('deduct-jar', [CustomController::class, 'deductJarQuantity']);
    Route::resource('production', StockProductionController::class)->except(['show']);
    Route::get('scrab-jar', [StockProductionController::class, 'scrabJar']);
});

// 👤 Customer Routes
Route::middleware('auth:customer_api')->prefix('customer')->group(function () {
    Route::resource('orders', CustomerOrderController::class)->except([
         'edit', 'destroy', 'update', 'show', 'store'
    ]);
    
    Route::get('products', [CustomerOrderController::class, 'products']);
    Route::get('shipping-addresses', [CustomerOrderController::class, 'shippingAddresses']);

    Route::get('orders/additional-order', [CustomerOrderController::class, 'getAdditionalOrders']);
    Route::post('orders/{type}/{id}', [CustomerOrderController::class, 'manageOrders']); // 'accept', 'cancel', 'additional-order' for this 3
    Route::get('orders/in-progress', [CustomerOrderController::class, 'requestOrderList']);
});

Route::middleware('auth:driver_api')->group(function () {
    Route::post('order_update/{id}', [OrderController::class, 'update']);
    Route::resource('profile', DriverController::class)->except([
        'create', 'edit', 'store','index'
    ]);
    Route::resource('maintenance', MaintenanceController::class)->except([
        'create', 'edit', 'destroy', 'update', 'show'
    ]);

    Route::get('jar-transportion-list', [StockProductionController::class, 'accept']);
    Route::get('jar-transportion/{id}', [StockProductionController::class, 'acceptId']);
});

Route::middleware('auth:distributor_api')->prefix('distributor')->group(function () {
    Route::get('/orders/summary', [DistributorOrderController::class, 'summary']);
    Route::post('/orders/{id}/accept', [DistributorOrderController::class, 'accept']);
    Route::post('/plant-wise-summery/{id}', [DistributorOrderController::class, 'plantWiseSummery']);
    Route::resource('orders', DistributorOrderController::class);
});