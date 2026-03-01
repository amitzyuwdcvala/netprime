<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PaymentController;

Route::prefix('payment')->middleware(['throttle:payment'])->group(function () {
    Route::post('/create-order', [PaymentController::class, 'create_order'])->name('api.payment.create-order');
    Route::post('/verify', [PaymentController::class, 'verify'])->name('api.payment.verify');
});
