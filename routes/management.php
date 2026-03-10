<?php

use App\Http\Controllers\Admin\ManagementController;
use App\Http\Controllers\Admin\RoleManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'ensure.not.deleted'])->prefix('admin/management')->name('admin.management.')->group(function () {
    Route::middleware('admin.or.subadmin')->group(function () {
        Route::inertia('/', 'admin/management/Portal')->name('portal');
    });

    Route::middleware('permission:users.manage')->group(function () {
        Route::get('users', [ManagementController::class, 'users'])->name('users.index');
        Route::delete('users/{user}', [ManagementController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('permission:subadmins.manage')->group(function () {
        Route::get('subadmins', [ManagementController::class, 'subadmins'])->name('subadmins.index');
        Route::post('subadmins', [ManagementController::class, 'storeSubadmin'])->name('subadmins.store');
        Route::delete('subadmins/{user}', [ManagementController::class, 'destroy'])->name('subadmins.destroy');
    });

    Route::middleware('permission:roles.manage')->group(function () {
        Route::get('roles', [RoleManagementController::class, 'index'])->name('roles.index');
        Route::post('roles', [RoleManagementController::class, 'store'])->name('roles.store');
        Route::put('roles/{role}', [RoleManagementController::class, 'update'])->name('roles.update');
    });

    Route::middleware('permission:sitemap.view')->get('sitemap', fn () => inertia('admin/management/Sitemap'))->name('sitemap');
});
