<?php

use App\Http\Controllers\Admin\ManagementController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\RoleTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'ensure.not.deleted', 'ensure.mfa', 'ensure.ip_allowlist', 'ensure.org'])->prefix('admin/management')->name('admin.management.')->group(function () {
    Route::middleware('permission:roles.view')->group(function () {
        Route::inertia('/', 'admin/management/Portal')->name('portal');
    });

    Route::middleware('permission:accounts.view')->group(function () {
        Route::get('users', [ManagementController::class, 'users'])->name('users.index');
    });
    Route::delete('users/{user}', [ManagementController::class, 'destroy'])
        ->middleware('permission:accounts.update')
        ->name('users.destroy');
    Route::post('users/{user}/assign-role', [ManagementController::class, 'assignRole'])
        ->middleware('permission:roles.edit')
        ->name('users.assign-role');

    Route::get('roles', [RoleManagementController::class, 'index'])
        ->middleware('permission:roles.view')
        ->name('roles.index');
    Route::post('roles', [RoleManagementController::class, 'store'])
        ->middleware('permission:roles.create')
        ->name('roles.store');
    Route::put('roles/{role}', [RoleManagementController::class, 'update'])
        ->middleware('permission:roles.edit')
        ->name('roles.update');
    Route::delete('roles/{role}', [RoleManagementController::class, 'destroy'])
        ->middleware('permission:roles.delete')
        ->name('roles.destroy');
    Route::get('roles/{role}/permissions', [RoleManagementController::class, 'permissions'])
        ->middleware('permission:permissions.view')
        ->name('roles.permissions');
    Route::put('roles/{role}/permissions', [RoleManagementController::class, 'updatePermissions'])
        ->middleware('permission:permissions.assign')
        ->name('roles.permissions.update');

    Route::get('role-templates', [RoleTemplateController::class, 'index'])
        ->middleware('permission:templates.view')
        ->name('templates.index');
    Route::post('role-templates', [RoleTemplateController::class, 'store'])
        ->middleware('permission:templates.create')
        ->name('templates.store');
    Route::post('role-templates/{template}/apply', [RoleTemplateController::class, 'apply'])
        ->middleware('permission:templates.apply')
        ->name('templates.apply');

    Route::middleware('permission:system.settings.view')->get('sitemap', fn () => inertia('admin/management/Sitemap'))->name('sitemap');
});
