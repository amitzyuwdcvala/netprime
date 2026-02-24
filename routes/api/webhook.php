<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\WebhookController;

/**
 * API Webhook Routes
 * Public routes - but signature verified
 */

Route::prefix('webhook')->group(function () {
    Route::post('/razorpay', [WebhookController::class, 'razorpay'])->name('api.webhook.razorpay');
    Route::post('/payu', [WebhookController::class, 'payu'])->name('api.webhook.payu');
    Route::post('/phonepe', [WebhookController::class, 'phonepe'])->name('api.webhook.phonepe');
    Route::post('/cashfree', [WebhookController::class, 'cashfree'])->name('api.webhook.cashfree');
});

