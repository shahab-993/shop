
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SaleController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authenticated Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Admin Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    // Sale Payment
    Route::post('/sales/{sale}/payments', [
        SaleController::class,
        'storePayment'
    ])->name('sales.payments.store');

});
