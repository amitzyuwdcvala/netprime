<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AdNetworkController;
use App\Http\Controllers\API\VideoAccessController;

Route::post('/video/access', [VideoAccessController::class, 'access'])
    ->middleware('throttle:video_access')
    ->name('api.video.access');

Route::prefix('self')->group(function () {

});

Route::get('/ads-settings', [AdNetworkController::class, 'get_ads_settings']);
