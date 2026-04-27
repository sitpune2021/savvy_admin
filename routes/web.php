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
use App\Http\Controllers\ReasonsController;
use App\Http\Controllers\RawMaterialsStockController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\LabReportController;
use App\Http\Controllers\DistributorController;
                            

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

Route::get('/digital', function () {
    try {
        $cards = [];
        return view('pdf.delivery_card', compact('cards'));
    } catch (\Exception $e) {
        return "schedule failed: " . $e->getMessage();
    }
});

Route::get('/scheduler', function () {
    try {
        Artisan::call('app:generate-contract-orders');
        $output = Artisan::output();
        return "All schedule have been run successfully! Output: " . nl2br($output);
    } catch (\Exception $e) {
        return "schedule failed: " . $e->getMessage();
    }
});

Route::get('/schedule', function () {
    Artisan::call('app:generate-contract-orders');
    return "schedule run!";
});

Route::get('/scheduler-additional-contracts', function () {
    try {
        Artisan::call('app:auto-accept-additional-contracts');
        $output = Artisan::output();
        return "Additional Contracts schedule have been run successfully! Output: " . nl2br($output);
    } catch (\Exception $e) {
        return "Additional Contracts schedule failed: " . $e->getMessage();
    }
});

Route::get('/dev/run/{action}', function ($action) {
    try {
        switch ($action) {
            case 'clear':
                Artisan::call('config:clear');
                Artisan::call('cache:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
                return "Cleared config, cache, route, and view.";

            case 'migrate':
                Artisan::call('migrate');
                return "Migration completed successfully!";

            case 'migrate-fresh':
                Artisan::call('migrate:fresh', ['--seed' => true]);
                return "Fresh migration and seed completed!";

            case 'seed':
                Artisan::call('db:seed');
                return "Database seeding completed!";
            case 'storage-link':
                Artisan::call('storage:link');
                $output = Artisan::output();
                return "Storage link created!"  . nl2br($output);
            case 'install':
                exec('composer install');
                return "composer install executed!";
            default:
                return "Invalid action: $action";
        }
    } catch (\Exception $e) {
        return "Error running action [$action]: " . $e->getMessage();
    }
});


Auth::routes(['register' => false]); 

Route::get('/privicy-policy', function () {
    return view('privicy-policy');
})->name('privicy-policy');


Route::middleware(['auth'])->group(function () {
    Route::middleware('role:admin|vendor|plant-manager')->group(function () {
        Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
        Route::get('/fetch-pending-orders', [App\Http\Controllers\HomeController::class, 'fetchPendingOrders'])->name('orders.fetch');
        Route::get('/yesterday-pending-orders-data', [App\Http\Controllers\HomeController::class, 'yesterdayPendingOrdersData'])->name('yesterday.pending.orders.data');
        Route::resource('plant', PlantController::class);
        Route::resource('route', RouteController::class);
        Route::resource('driver', DriverController::class);
        Route::resource('customer', CustomerController::class);
        Route::resource('order', OrderController::class);
        Route::resource('raw-materials', RawMaterialsStockController::class);
        Route::get('raw-materials/{id}/distribute', [RawMaterialsStockController::class, 'distribute'])->name('raw-materials.distribute');
        Route::get('raw-materials/{id}/purches-distribute', [RawMaterialsStockController::class, 'purchesDistribute'])->name('raw-materials.purches-distribute');
        Route::post('/download-digital-cards-zip', [App\Http\Controllers\HomeController::class, 'downloadCardZip'])->name('downloaddigitalcardszip');

        Route::put('customer/{id}/vendor-shipping-address', [CustomerController::class, 'updateShippingAddressForVendor'])->name('customer.update-shipping-address-forr-vendor');

    });
    Route::middleware('role:admin')->group(function () {
        Route::get('/order/{id}/assign-driver', [OrderController::class, 'assignDriver'])->name('order.assign-driver');
        Route::get('customer/{id}/assign-route', [CustomerController::class, 'assignRoute'])->name('customer.assign-route');

        Route::resource('product', ProductController::class);
        Route::resource('lab-reports', LabReportController::class);
        Route::resource('dispensary', DispensaryController::class);
        Route::resource('request-order', RequestOrdersController::class);
        Route::resource('maintenance', MaintenanceController::class);
        Route::resource('vendor', VendorController::class);
        Route::resource('distributor', DistributorController::class);
        Route::resource('reasons', ReasonsController::class);
        Route::post('reports/export', [ReportsController::class, 'reports'])->name('reports.export');

        Route::resource('reports', ReportsController::class);
        Route::put('/request-order/{id}/status', [RequestOrdersController::class, 'updateStatus'])->name('requestOrder.update.status');
        Route::put('/maintenance/{id}/status', [MaintenanceController::class, 'updateStatus'])->name('maintenance.update.status');
        Route::put('customer/{id}/shipping-address', [CustomerController::class, 'storeUpdateShippingAddress'])->name('customer.store-update-shipping-address');
        
        Route::post('assign-route', [OrderController::class, 'storeRoute'])->name('order.store-route');
    });
});
