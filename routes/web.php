<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/** Authentication Routes */
require __DIR__ . '/web/auth.php';

/** Admin Routes (protected) */
Route::middleware(['auth:admin', 'prevent_back_history'])
    ->name('admin.')
    ->prefix('admin')
    ->group(function () {
        require __DIR__ . '/web/admin.php';
    });
