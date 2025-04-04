<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Artisan;

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

Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    return "Cache cleared!";
});

// Route::get('/migration', function () {
//     try {
//         Artisan::call('migrate');
//         return "Migration completed successfully!";
//     } catch (\Exception $e) {
//         return "Migration failed: " . $e->getMessage();
//     }
// });

// Route::get('/run-all-seeders', function () {
//     $exitCode = Artisan::call('db:seed');
//     $output = Artisan::output();
//     return "All seeders have been run successfully! Output: " . nl2br($output);
// });

// Route::get('/create-controller', function () {
//     $exitCode = Artisan::call('make:controller DriverController --resource');

//     $output = Artisan::output();
    
//     return "Controller has been created successfully! Output: <pre>" . nl2br(e($output)) . "</pre>";
// });


Route::get('/fix-assets', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return "Assets fixed!";
});

Auth::routes(['register' => false]); 

Route::middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::resource('customer', CustomerController::class);
    Route::resource('driver', DriverController::class);
    Route::resource('order', OrderController::class);


});
