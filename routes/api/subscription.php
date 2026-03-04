<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SubscriptionController;

// GET subscription/plans is registered in api.php as public (no auth required)
Route::prefix('subscription')->group(function () {
    // Other subscription routes (if any) go here; plans is public in api.php
});
