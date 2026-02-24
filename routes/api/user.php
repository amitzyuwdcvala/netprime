<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AdNetworkController;
use App\Http\Controllers\API\VideoAccessController;

/**
 * API User Routes
 * Protected routes - android_auth required
 */

Route::post('/video/access', [VideoAccessController::class, 'access'])->name('api.video.access');

Route::prefix('self')->group(function () {

});

/** Ad Settings (public for now - move to auth if needed) */
Route::get('/ads-settings', [AdNetworkController::class, 'get_ads_settings']);
