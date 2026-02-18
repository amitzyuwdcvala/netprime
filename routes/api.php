<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['route_classifier'])->group(function () {

    /** Public auth routes */
    require __DIR__ . '/api/auth.php';

    /** Protected routes */
    Route::middleware(['auth:sanctum'])->group(function () {
        require __DIR__ . '/api/user.php';
        require __DIR__ . '/api/subscription.php';
        require __DIR__ . '/api/payment.php';
    });

    /** Webhook endpoints */
    Route::prefix('webhook')->group(function () {
        // Route::post('razorpay', [WebhookController::class, 'razorpay'])->name('webhook.razorpay');
    });
});
