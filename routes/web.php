<?php

use Illuminate\Support\Facades\Route;
use App\Models\PaymentGateway;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/**
 * Test Razorpay Checkout (for local testing only – remove or guard in production)
 * Open in browser: http://localhost:8000/test-checkout
 */
Route::get('/test-checkout', function () {
    $gateway = PaymentGateway::where('is_active', true)->first();
    $razorpay_key = null;
    if ($gateway && strtolower($gateway->name) === 'razorpay' && !empty($gateway->credentials['key_id'])) {
        $razorpay_key = $gateway->credentials['key_id'];
    }
    return view('test-checkout', ['razorpay_key' => $razorpay_key]);
})->name('test-checkout');

/** Authentication Routes */
require __DIR__ . '/web/auth.php';

/** Admin Routes (protected) */
Route::middleware(['auth:admin', 'prevent_back_history'])
    ->name('admin.')
    ->prefix('admin')
    ->group(function () {
        require __DIR__ . '/web/admin.php';
    });
