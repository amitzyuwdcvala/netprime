<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['route_classifier'])->group(function () {
    require __DIR__ . '/api/auth.php';

    Route::get('subscription/plans', [\App\Http\Controllers\API\SubscriptionController::class, 'get_plans'])
        ->name('api.subscription.plans');

    Route::get('app-config', [\App\Http\Controllers\API\AppConfigController::class, 'show'])
        ->name('api.app-config');

    Route::get('payment/phonepe/callback', [\App\Http\Controllers\API\PaymentController::class, 'phonepeCallback'])
        ->name('api.payment.phonepe.callback');

    Route::get('payment/cashfree/callback', [\App\Http\Controllers\API\PaymentController::class, 'cashfreeCallback'])
        ->name('api.payment.cashfree.callback');

    Route::middleware(['android_auth'])->group(function () {
        require __DIR__ . '/api/user.php';
        require __DIR__ . '/api/subscription.php';
        require __DIR__ . '/api/payment.php';
    });
    require __DIR__ . '/api/webhook.php';
});