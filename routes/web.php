<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AuditExportController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\IpAllowlistController;
use App\Http\Controllers\Admin\SecurityDashboardController;
use App\Http\Controllers\Admin\WebhookController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\RoleAuthController;
use App\Http\Controllers\OrganizationSwitchController;
use App\Models\Role;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $roles = Role::orderBy('name')->get(['id', 'name', 'slug', 'route', 'is_default']);
    $defaultRole = $roles->firstWhere('is_default', true);

    return Inertia::render('Landing', [
        'roles' => $roles,
        'defaultRole' => $defaultRole,
    ]);
})->name('home');

Route::get('login/{role}', [RoleAuthController::class, 'login'])->name('role.login');
Route::get('register/{role}', [RoleAuthController::class, 'register'])->name('role.register');

Route::post('organizations/switch', OrganizationSwitchController::class)
    ->middleware(['auth', 'ensure.not.deleted', 'ensure.mfa', 'ensure.ip_allowlist', 'ensure.org'])
    ->name('organizations.switch');

Route::middleware(['auth', 'verified', 'ensure.not.deleted', 'ensure.mfa', 'ensure.ip_allowlist', 'ensure.org', 'permission:dashboard.read'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
    Route::get('admin/security', [SecurityDashboardController::class, 'index'])
        ->middleware('permission:security.sessions.view')
        ->name('admin.security');
    Route::delete('admin/security/sessions/{sessionId}', [SecurityDashboardController::class, 'destroySession'])
        ->middleware('permission:security.sessions.revoke')
        ->name('admin.security.sessions.destroy');
    Route::get('admin/security/allowlist', [IpAllowlistController::class, 'index'])
        ->middleware('permission:security.ip_allowlist.view')
        ->name('admin.security.allowlist');
    Route::post('admin/security/allowlist', [IpAllowlistController::class, 'store'])
        ->middleware('permission:security.ip_allowlist.manage')
        ->name('admin.security.allowlist.store');
    Route::put('admin/security/allowlist/roles/{role}', [IpAllowlistController::class, 'updateRole'])
        ->middleware('permission:security.ip_allowlist.manage')
        ->name('admin.security.allowlist.roles.update');
    Route::delete('admin/security/allowlist/{entry}', [IpAllowlistController::class, 'destroy'])
        ->middleware('permission:security.ip_allowlist.manage')
        ->name('admin.security.allowlist.destroy');
    Route::get('admin/settings', [SettingsController::class, 'index'])
        ->middleware('permission:system.settings.view')
        ->name('admin.settings');
    Route::patch('admin/settings', [SettingsController::class, 'update'])
        ->middleware('permission:system.settings.update')
        ->name('admin.settings.update');
    Route::get('admin/webhooks', [WebhookController::class, 'index'])
        ->middleware('permission:webhooks.view')
        ->name('admin.webhooks');
    Route::post('admin/webhooks', [WebhookController::class, 'store'])
        ->middleware('permission:webhooks.manage')
        ->name('admin.webhooks.store');
    Route::delete('admin/webhooks/{endpoint}', [WebhookController::class, 'destroy'])
        ->middleware('permission:webhooks.manage')
        ->name('admin.webhooks.destroy');
    Route::get('admin/approvals', [ApprovalController::class, 'index'])
        ->middleware('permission:approvals.view')
        ->name('admin.approvals');
    Route::post('admin/approvals/{approval}/approve', [ApprovalController::class, 'approve'])
        ->middleware('permission:approvals.approve')
        ->name('admin.approvals.approve');
    Route::get('activity', [ActivityController::class, 'self'])
        ->middleware('permission:audit.view')
        ->name('activity.self');
    Route::get('admin/activity', [ActivityController::class, 'all'])
        ->middleware('permission:audit.export')
        ->name('activity.all');
    Route::get('admin/audit/export', [AuditExportController::class, 'export'])
        ->middleware('permission:audit.export')
        ->name('admin.audit.export');
});

require __DIR__.'/settings.php';
require __DIR__.'/management.php';
