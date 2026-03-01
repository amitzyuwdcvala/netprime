<?php

use Illuminate\Support\Facades\Route;
use App\Models\PaymentGateway;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| / = Landing page (netprime.store)
| /admin = Admin panel (login, dashboard, settings, test-checkout)
| /api/v1/* = Mobile API (payment, plans, video access, webhook) - in api.php
*/

/** Landing page (root) – same as netprime_maindomain index */
Route::get('/', function () {
    return view('landing');
})->name('home');

/** Redirect legacy /login to /admin/login (so framework redirect to "login" still works) */
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

/**
 * Admin: auth (login/logout) and all admin/dashboard under /admin
 */
Route::prefix('admin')->name('admin.')->group(function () {
    /** Auth: login form and submit (guest only), logout (auth only) */
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
    });
    Route::post('logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout')->middleware('auth:admin');

    /** Test Razorpay Checkout (for local/testing – under admin) */
    Route::get('test-checkout', function () {
        $gateway = PaymentGateway::where('is_active', true)->first();
        $razorpay_key = null;
        if ($gateway && strtolower($gateway->name) === 'razorpay' && !empty($gateway->credentials['key_id'])) {
            $razorpay_key = $gateway->credentials['key_id'];
        }
        return view('test-checkout', ['razorpay_key' => $razorpay_key]);
    })->name('test-checkout')->middleware('auth:admin');

    /** Admin dashboard and settings (protected) */
    Route::middleware(['auth:admin', 'prevent_back_history'])->group(function () {
        require __DIR__ . '/web/admin.php';
    });
});
