<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

/**
 * API Authentication Routes
 * Public routes - no auth required
 */

Route::post('/register', [AuthController::class, 'register'])->name('api.register');