<?php

use App\Http\Controllers\VideoAccessController;
use Illuminate\Support\Facades\Route;

/**
 * API Authentication Routes
 * Public routes - no auth required
 */

Route::post('/access-video', [VideoAccessController::class, 'accessVideo'])->name('api-access-video');
