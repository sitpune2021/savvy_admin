<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PlantController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Vendor\CustomerController as VendorCustomerController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\RequestOrdersController;
use App\Http\Controllers\DispensaryController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\VendorController;

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

Route::get('/image-check', [MaintenanceController::class, 'check']);
Route::post('/upload-image', [MaintenanceController::class, 'upload'])->name('image.upload');


Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    return "Cache cleared!";
});

Route::get('/migration', function () {
    try {
        Artisan::call('migrate');
        return "Migration completed successfully";
    } catch (\Exception $e) {
        return "Migration failed: " . $e->getMessage();
    }
});

Route::get('/migration_fresh', function () {
    try {
        Artisan::call('migrate:fresh');
        Artisan::call('migrate --seed');
        Artisan::call('db:seed');
        return "Migration completed successfully";
    } catch (\Exception $e) {
        return "Migration failed: " . $e->getMessage();
    }
});

Route::get('/scheduler', function () {
    try {
        Artisan::call('schedule:run');
        $output = Artisan::output();
        return "All schedule have been run successfully! Output: " . nl2br($output);
    } catch (\Exception $e) {
        return "schedule failed: " . $e->getMessage();
    }
});

Route::get('/run-all-seeders', function () {
    $exitCode = Artisan::call('db:seed');
    $output = Artisan::output();
    return "All seeders have been run successfully! Output: " . nl2br($output);
});

Route::get('/storage-link', function () {
    $exitCode = Artisan::call('storage:link');
    $output = Artisan::output();
    return "All seeders have been run successfully! Output: " . nl2br($output);
});

Route::get('/schedule', function () {
    Artisan::call('schedule:run');
    return "schedule run!";
});

Route::get('/fix-assets', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return "Assets fixed!";
});

Auth::routes(['register' => false]); 

Route::get('/privicy-policy', function () {
    return view('privicy-policy');
})->name('privicy-policy');


Route::middleware(['auth'])->group(function () {
    Route::middleware('role:admin|vendor|plant-manager')->group(function () {
        Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
        Route::resource('plant', PlantController::class);
        Route::resource('route', RouteController::class);
        Route::resource('driver', DriverController::class);
        Route::resource('customer', CustomerController::class);
        Route::resource('order', OrderController::class);

        Route::put('customer/{id}/vendor-shipping-address', [CustomerController::class, 'updateShippingAddressForVendor'])->name('customer.update-shipping-address-forr-vendor');
        Route::get('/fetch-pending-orders', [App\Http\Controllers\HomeController::class, 'fetchPendingOrders'])->name('orders.fetch');


    });
    Route::middleware('role:admin')->group(function () {
        Route::get('/order/{id}/assign-driver', [OrderController::class, 'assignDriver'])->name('order.assign-driver');
        Route::get('customer/{id}/assign-route', [CustomerController::class, 'assignRoute'])->name('customer.assign-route');

        Route::resource('product', ProductController::class);
        Route::resource('dispensary', DispensaryController::class);
        Route::resource('request-order', RequestOrdersController::class);
        Route::resource('maintenance', MaintenanceController::class);
        Route::resource('vendor', VendorController::class);

        Route::put('/request-order/{id}/status', [RequestOrdersController::class, 'updateStatus'])->name('requestOrder.update.status');
        Route::put('/maintenance/{id}/status', [MaintenanceController::class, 'updateStatus'])->name('maintenance.update.status');
        Route::put('customer/{id}/shipping-address', [CustomerController::class, 'storeUpdateShippingAddress'])->name('customer.store-update-shipping-address');
        
        Route::post('assign-route', [OrderController::class, 'storeRoute'])->name('order.store-route');
    });
});
