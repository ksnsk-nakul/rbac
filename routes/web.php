<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AuditExportController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\IpAllowlistController;
use App\Http\Controllers\Admin\IntegrationsController;
use App\Http\Controllers\Admin\SecurityDashboardController;
use App\Http\Controllers\Admin\WebhookController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\SupportTicketController as AdminSupportTicketController;
use App\Http\Controllers\Auth\RoleAuthController;
use App\Http\Controllers\OrganizationSwitchController;
use App\Http\Controllers\Webhooks\RazorpayWebhookController;
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

Route::post('webhooks/razorpay', RazorpayWebhookController::class)->name('webhooks.razorpay');

Route::middleware(['auth', 'verified', 'ensure.not.deleted', 'ensure.mfa', 'ensure.ip_allowlist', 'ensure.org', 'ensure.license', 'permission:dashboard.read'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
    Route::get('admin/billing', [BillingController::class, 'index'])
        ->middleware('permission:billing.view')
        ->name('admin.billing');
    Route::post('admin/billing/checkout/{plan}', [BillingController::class, 'checkout'])
        ->middleware('permission:billing.manage')
        ->name('admin.billing.checkout');
    Route::post('admin/billing/subscriptions/{subscription}/cancel', [BillingController::class, 'cancel'])
        ->middleware('permission:billing.manage')
        ->name('admin.billing.cancel');
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
    Route::get('admin/integrations', [IntegrationsController::class, 'index'])
        ->middleware('permission:integrations.view')
        ->name('admin.integrations');
    Route::patch('admin/integrations', [IntegrationsController::class, 'update'])
        ->middleware('permission:integrations.update')
        ->name('admin.integrations.update');
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

    Route::get('admin/support', [AdminSupportTicketController::class, 'index'])
        ->middleware('permission:support.manage')
        ->name('admin.support');
    Route::get('admin/support/{ticket}', [AdminSupportTicketController::class, 'show'])
        ->middleware('permission:support.manage')
        ->name('admin.support.show');
    Route::post('admin/support/{ticket}/messages', [AdminSupportTicketController::class, 'storeMessage'])
        ->middleware('permission:support.manage')
        ->name('admin.support.messages.store');
    Route::patch('admin/support/{ticket}', [AdminSupportTicketController::class, 'update'])
        ->middleware('permission:support.manage')
        ->name('admin.support.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/management.php';
