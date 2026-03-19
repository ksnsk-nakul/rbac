<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\ApprovalService;
use App\Services\ActivityLogger;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleManagementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/management/Roles', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:roles,slug'],
            'route' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:roles,route'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        if ($user && ApprovalService::requiresApproval($user)) {
            ApprovalService::create('role.create', $validated, $user);

            return back()->with('status', 'Role creation requires approval.');
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'route' => $validated['route'],
            'is_subadmin' => false,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        if ($role->is_default) {
            Role::where('id', '!=', $role->id)->update(['is_default' => false]);
        }

        ActivityLogger::log('role.created', $role, 'Role created');

        AuditLogger::log(
            'role.created',
            'role',
            $role,
            'Role created'
        );

        return back()->with('status', 'Role created.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:roles,slug,'.$role->id],
            'route' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:roles,route,'.$role->id],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        if ($user && ApprovalService::requiresApproval($user)) {
            ApprovalService::create('role.update', array_merge($validated, ['role_id' => $role->id]), $user);

            return back()->with('status', 'Role update requires approval.');
        }

        $role->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'route' => $validated['route'],
            'is_default' => $validated['is_default'] ?? false,
        ]);

        if ($role->is_default) {
            Role::where('id', '!=', $role->id)->update(['is_default' => false]);
        }

        ActivityLogger::log('role.updated', $role, 'Role updated');

        AuditLogger::log(
            'role.updated',
            'role',
            $role,
            'Role updated'
        );

        return back()->with('status', 'Role updated.');
    }

    public function permissions(Role $role): Response
    {
        return Inertia::render('admin/management/RolePermissions', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::orderBy('main_group')->orderBy('name')->get(),
        ]);
    }

    public function updatePermissions(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        ActivityLogger::log('permissions.changed', $role, 'Permissions updated');

        AuditLogger::log(
            'permissions.changed',
            'role',
            $role,
            'Permissions updated',
            ['permissions' => $validated['permissions'] ?? []]
        );

        return back()->with('status', 'Permissions updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $user = request()->user();
        if ($role->slug === 'admin') {
            return back()->with('status', 'Admin role cannot be deleted.');
        }

        if ($role->is_default) {
            return back()->with('status', 'Default role cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('status', 'Role has users assigned and cannot be deleted.');
        }

        if ($user && ApprovalService::requiresApproval($user)) {
            ApprovalService::create('role.delete', ['role_id' => $role->id], $user);

            return back()->with('status', 'Role deletion requires approval.');
        }

        ActivityLogger::log('role.deleted', $role, 'Role deleted');

        AuditLogger::log('role.deleted', 'role', $role, 'Role deleted');

        $role->delete();

        return back()->with('status', 'Role deleted.');
    }
}
