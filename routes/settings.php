<?php

use App\Http\Controllers\Account\ActivityController as AccountActivityController;
use App\Http\Controllers\Account\ApiTokenController;
use App\Http\Controllers\Account\ProfileController as AccountProfileController;
use App\Http\Controllers\Account\SessionController as AccountSessionController;
use App\Http\Controllers\Account\SecurityController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ensure.not.deleted', 'ensure.mfa', 'ensure.ip_allowlist', 'ensure.org'])->group(function () {
    Route::redirect('settings', '/account/settings/profile');
    Route::redirect('settings/profile', '/account/settings/profile');
    Route::redirect('settings/password', '/account/settings/security');
    Route::redirect('settings/two-factor', '/account/settings/security/mfa');
    Route::redirect('settings/appearance', '/account/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified', 'ensure.not.deleted', 'ensure.mfa', 'ensure.ip_allowlist', 'ensure.org'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');

});

Route::middleware(['auth', 'verified', 'ensure.not.deleted', 'ensure.mfa', 'ensure.ip_allowlist', 'ensure.org'])->prefix('account/settings')->name('account.settings.')->group(function () {
    Route::get('profile', [AccountProfileController::class, 'edit'])->name('profile');
    Route::patch('profile', [AccountProfileController::class, 'update'])->name('profile.update');

    Route::get('security', [SecurityController::class, 'edit'])->name('security');
    Route::put('security/password', [SecurityController::class, 'updatePassword'])
        ->middleware('throttle:6,1')
        ->name('security.password');
    Route::post('security/logout-other-sessions', [SecurityController::class, 'logoutOtherSessions'])
        ->middleware('throttle:6,1')
        ->name('security.logout-other-sessions');
    Route::get('security/mfa', [TwoFactorAuthenticationController::class, 'show'])
        ->middleware('permission:security.mfa.manage')
        ->name('security.mfa');
    Route::post('security/trusted-devices', [SecurityController::class, 'storeTrustedDevice'])
        ->middleware('permission:security.mfa.manage')
        ->name('security.trusted-devices.store');
    Route::delete('security/trusted-devices/{device}', [SecurityController::class, 'destroyTrustedDevice'])
        ->middleware('permission:security.mfa.manage')
        ->name('security.trusted-devices.destroy');

    Route::get('activity', [AccountActivityController::class, 'index'])
        ->middleware('permission:audit.view')
        ->name('activity');

    Route::get('sessions', [AccountSessionController::class, 'index'])
        ->middleware('permission:security.sessions.view')
        ->name('sessions');
    Route::delete('sessions/{sessionId}', [AccountSessionController::class, 'destroy'])
        ->middleware('permission:security.sessions.view')
        ->name('sessions.destroy');

    Route::get('api-tokens', [ApiTokenController::class, 'index'])
        ->middleware('permission:api_tokens.view')
        ->name('api-tokens');
    Route::post('api-tokens', [ApiTokenController::class, 'store'])
        ->middleware('permission:api_tokens.create')
        ->name('api-tokens.store');
    Route::delete('api-tokens/{token}', [ApiTokenController::class, 'destroy'])
        ->middleware('permission:api_tokens.revoke')
        ->name('api-tokens.destroy');
});
