<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PaymentController;

/**
 * API Payment Routes
 * Protected routes - android_auth required
 * Rate limited: 10 requests/min per user (android_id) to handle concurrency and prevent abuse
 */

Route::prefix('payment')->middleware(['throttle:payment'])->group(function () {
    Route::post('/create-order', [PaymentController::class, 'create_order'])->name('api.payment.create-order');
    Route::post('/verify', [PaymentController::class, 'verify'])->name('api.payment.verify');
});
