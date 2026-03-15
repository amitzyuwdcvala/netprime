<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ActiveToggleController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GatewayController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('toggle-active', [ActiveToggleController::class, 'toggle'])->name('toggle.active');
Route::get('gateways', [GatewayController::class, 'index'])->name('gateways.index');
Route::get('gateways/export', [GatewayController::class, 'export'])->name('gateways.export');
Route::post('gateways/manage', [GatewayController::class, 'manage_gateway'])->name('gateways.manage');
Route::post('gateways/save', [GatewayController::class, 'save_gateway'])->name('gateways.save');
Route::post('gateways/delete', [GatewayController::class, 'delete_gateway'])->name('gateways.delete');
Route::get('users', [UserController::class, 'index'])->name('users.index');
Route::post('users/manage', [UserController::class, 'manage_user'])->name('users.manage');
Route::post('users/save', [UserController::class, 'save_user'])->name('users.save');
Route::post('users/delete', [UserController::class, 'delete_user'])->name('users.delete');
Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
Route::get('plans/export', [PlanController::class, 'export'])->name('plans.export');
Route::post('plans/manage', [PlanController::class, 'manage_plan'])->name('plans.manage');
Route::post('plans/save', [PlanController::class, 'save_plan'])->name('plans.save');
Route::post('plans/delete', [PlanController::class, 'delete_plan'])->name('plans.delete');
Route::get('users/export', [UserController::class, 'export'])->name('users.export');
Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
Route::get('payments/export', [PaymentController::class, 'export'])->name('payments.export');
Route::get('payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
Route::post('payments/detail', [PaymentController::class, 'show_detail'])->name('payments.detail');
Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
Route::post('settings/manage', [SettingController::class, 'manage_setting'])->name('manage.setting');
Route::post('settings/save', [SettingController::class, 'save_setting'])->name('save.setting');
Route::post('settings/delete', [SettingController::class, 'delete_setting'])->name('delete.setting');

Route::get('config', [ConfigController::class, 'index'])->name('config.index');
Route::post('config/save', [ConfigController::class, 'save'])->name('config.save');
