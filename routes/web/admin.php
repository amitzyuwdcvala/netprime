<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| All routes already prefixed with 'admin' and named 'admin.'
*/

/** Dashboard */
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

/** Settings Management */
Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
Route::post('settings/manage', [SettingController::class, 'manage_setting'])->name('manage.setting');
Route::post('settings/save', [SettingController::class, 'save_setting'])->name('save.setting');
Route::post('settings/delete', [SettingController::class, 'delete_setting'])->name('delete.setting');
