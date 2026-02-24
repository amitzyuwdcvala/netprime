<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SubscriptionController;

/**
 * API Subscription Routes
 * Protected routes - android_auth required
 */

Route::prefix('subscription')->group(function () {
    Route::get('/plans', [SubscriptionController::class, 'get_plans'])->name('api.subscription.plans');
});
