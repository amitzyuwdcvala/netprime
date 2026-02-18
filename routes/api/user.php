<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AdNetworkController;

/**
 * API User Routes
 * Protected routes - auth:sanctum required
 */

Route::prefix('self')->group(function () {
    // Route::get('details', [UserController::class, 'self_information']);
    // Route::get('profile', [UserController::class, 'view_profile']);
    // Route::post('profile', [UserController::class, 'manage_profile']);
});

/** Ad Settings (public for now - move to auth if needed) */
Route::get('/ads-settings', [AdNetworkController::class, 'get_ads_settings']);
