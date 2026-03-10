<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user()?->loadMissing('role.permissions');

        $canRoles = $user?->hasPermission('roles.manage') ?? false;
        $canUsers = $user?->hasPermission('users.manage') ?? false;

        $roles = [];
        $permissions = [];

        if ($canRoles) {
            $roles = Role::with('permissions')
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
                ]);

            $permissions = Permission::orderBy('name')
                ->get(['id', 'name', 'slug']);
        }

        $usersCount = $canUsers
            ? User::whereHas('role', fn ($query) => $query->where('slug', 'user'))->count()
            : null;

        $adminUsers = $user && $user->isAdmin()
            ? User::with('role')
                ->whereHas('role', fn ($query) => $query->whereIn('slug', ['admin', 'subadmin']))
                ->orderBy('name')
                ->get()
                ->map(fn (User $account) => [
                    'id' => $account->id,
                    'name' => $account->name,
                    'email' => $account->email,
                    'role' => $account->role?->slug,
                ])
            : [];

        return Inertia::render('admin/Dashboard', [
            'roles' => $roles,
            'permissions' => $permissions,
            'usersCount' => $usersCount,
            'adminUsers' => $adminUsers,
            'canRoles' => $canRoles,
            'canUsers' => $canUsers,
        ]);
    }
}
