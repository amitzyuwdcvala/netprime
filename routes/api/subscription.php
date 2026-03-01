<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SubscriptionController;

Route::prefix('subscription')->group(function () {
    Route::get('/plans', [SubscriptionController::class, 'get_plans'])->name('api.subscription.plans');
});
