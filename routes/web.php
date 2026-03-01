<?php

use Illuminate\Support\Facades\Route;
use App\Models\PaymentGateway;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
    });
    Route::post('logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout')->middleware('auth:admin');

    Route::get('test-checkout', function () {
        $gateway = PaymentGateway::where('is_active', true)->first();
        $razorpay_key = null;
        if ($gateway && strtolower($gateway->name) === 'razorpay' && !empty($gateway->credentials['key_id'])) {
            $razorpay_key = $gateway->credentials['key_id'];
        }
        return view('test-checkout', ['razorpay_key' => $razorpay_key]);
    })->name('test-checkout')->middleware('auth:admin');

    Route::middleware(['auth:admin', 'prevent_back_history'])->group(function () {
        require __DIR__ . '/web/admin.php';
    });
});
