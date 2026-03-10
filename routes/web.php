<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'auth/UserLogin', [
    'canResetPassword' => Features::enabled(Features::resetPasswords()),
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::inertia('admin/login', 'auth/AdminLogin', [
    'role' => 'admin',
])->name('admin.login');
Route::inertia('subadmin/login', 'auth/AdminLogin', [
    'role' => 'subadmin',
])->name('subadmin.login');

Route::inertia('user/login', 'auth/UserLogin', [
    'canResetPassword' => Features::enabled(Features::resetPasswords()),
    'canRegister' => Features::enabled(Features::registration()),
])->name('user.login');

Route::inertia('user/register', 'auth/UserRegister')->name('user.register');

Route::middleware(['auth', 'verified', 'ensure.not.deleted', 'permission:dashboard.read'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('admin/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])
        ->middleware('admin.or.subadmin')
        ->name('admin.dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/management.php';
