<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ApprovalRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user()?->loadMissing('role.permissions');
        $canRoles = $user?->hasPermission('roles.view') ?? false;
        $canUsers = $user?->hasPermission('accounts.view') ?? false;
        $canActivityAll = ($user?->isAdmin() ?? false) && ($user?->hasPermission('audit.export') ?? false);

        $roles = $canRoles
            ? Role::with('permissions')
                ->withCount('users')
                ->orderBy('name')
                ->get()
                ->map(fn (Role $role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'users_count' => $role->users_count,
                    'permissions' => $role->permissions->map(fn (Permission $permission) => [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'slug' => $permission->slug,
                    ]),
                ])
            : [];

        $permissions = $canRoles
            ? Permission::orderBy('name')->get(['id', 'name', 'slug'])
            : [];

        $usersCount = User::count();

        $adminUsers = $user && $user->isAdmin()
            ? User::with('role')
                ->whereHas('role', fn ($query) => $query->whereIn('slug', ['super_admin']))
                ->orderBy('name')
                ->get()
                ->map(fn (User $account) => [
                    'id' => $account->id,
                    'name' => $account->name,
                    'email' => $account->email,
                    'role' => $account->role?->slug,
                ])
            : [];

        $recentActivity = $canActivityAll
            ? ActivityLog::with('user')
                ->orderByDesc('id')
                ->limit(12)
                ->get()
                ->map(fn (ActivityLog $log) => [
                    'id' => $log->id,
                    'email' => $log->user?->email,
                    'event' => $log->action,
                    'role' => $log->user?->role?->slug,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at?->toDateTimeString(),
                ])
            : [];

        return Inertia::render('admin/Dashboard', [
            'usersCount' => $usersCount,
            'rolesCount' => Role::count(),
            'permissionsCount' => Permission::count(),
            'pendingApprovalsCount' => ApprovalRequest::where('status', 'pending')->count(),
            'openSupportTicketsCount' => SupportTicket::where('status', '!=', 'closed')->count(),
            'adminUsers' => $adminUsers,
            'canRoles' => $canRoles,
            'canUsers' => $canUsers,
            'canActivityAll' => $canActivityAll,
            'recentActivity' => $recentActivity,
        ]);
    }
}
