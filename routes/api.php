<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['route_classifier'])->group(function () {
    require __DIR__ . '/api/auth.php';

    // Plans are public so subscription screen can load without requiring registration
    Route::get('subscription/plans', [\App\Http\Controllers\API\SubscriptionController::class, 'get_plans'])
        ->name('api.subscription.plans');

    Route::middleware(['android_auth'])->group(function () {
        require __DIR__ . '/api/user.php';
        require __DIR__ . '/api/subscription.php';
        require __DIR__ . '/api/payment.php';
    });
    require __DIR__ . '/api/webhook.php';
});

