<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\IntegrationsController;
use App\Http\Controllers\Admin\ModulesController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'ensure.not.deleted', 'ensure.mfa', 'ensure.ip_allowlist'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])
            ->middleware('permission:dashboard.view')
            ->name('dashboard');

        Route::get('activity', [ActivityController::class, 'all'])
            ->middleware('permission:settings.view')
            ->name('activity');

        Route::get('users', [UsersController::class, 'index'])
            ->middleware('permission:users.view')
            ->name('users.index');
        Route::post('users', [UsersController::class, 'store'])
            ->middleware('permission:users.add')
            ->name('users.store');
        Route::patch('users/{user}', [UsersController::class, 'update'])
            ->middleware('permission:users.update')
            ->name('users.update');
        Route::post('users/{user}/ban', [UsersController::class, 'ban'])
            ->middleware('permission:users.ban')
            ->name('users.ban');
        Route::post('users/{user}/restore', [UsersController::class, 'restore'])
            ->middleware('permission:users.ban')
            ->name('users.restore');
        Route::delete('users/{user}', [UsersController::class, 'destroy'])
            ->middleware('permission:users.delete')
            ->name('users.destroy');

        Route::get('modules', [ModulesController::class, 'index'])
            ->middleware('permission:modules.view')
            ->name('modules.index');
        Route::patch('modules/{module}', [ModulesController::class, 'update'])
            ->middleware('permission:modules.update')
            ->name('modules.update');

        Route::get('settings', [SettingsController::class, 'index'])
            ->middleware('permission:settings.view')
            ->name('settings.index');
        Route::patch('settings', [SettingsController::class, 'update'])
            ->middleware('permission:settings.update')
            ->name('settings.update');

        Route::get('integrations', [IntegrationsController::class, 'index'])
            ->middleware('permission:integrations.view')
            ->name('integrations.index');
        Route::patch('integrations', [IntegrationsController::class, 'update'])
            ->middleware('permission:integrations.update')
            ->name('integrations.update');
    });
