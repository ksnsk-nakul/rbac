<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\Users\UserListCriteria;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\AuditLogger;
use App\Services\PlanGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ManagementController extends Controller
{
    /**
     * List users.
     */
    public function users(Request $request, UserRepositoryInterface $usersRepo): Response
    {
        $actor = $request->user();

        $criteria = new UserListCriteria(
            q: (string) $request->query('q', ''),
            role: ($request->query('role') !== null && (string) $request->query('role') !== '') ? (string) $request->query('role') : null,
            status: (string) $request->query('status', 'all'),
            sort: (string) $request->query('sort', 'name'),
            dir: (string) $request->query('dir', 'asc'),
            perPage: (int) $request->query('per_page', 15),
            view: (string) $request->query('view', 'list'),
        );

        $users = $usersRepo
            ->paginateForAdminManagement($actor, $criteria)
            ->appends($request->query());

        return Inertia::render('admin/management/Users', [
            'users' => $users,
            'roleLabel' => 'Users',
            'roles' => Role::orderBy('name')->get(['id', 'name', 'slug']),
            'filters' => [
                'q' => $criteria->q,
                'role' => $criteria->role,
                'status' => $criteria->status,
                'view' => $criteria->view,
            ],
        ]);
    }

    /**
     * Soft-delete a user so they cannot login or register again.
     * Only non-admin users can be deleted.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            return back()->with('status', 'You cannot remove your own account.');
        }

        if ($user->isAdmin()) {
            abort(403, 'Cannot remove an admin.');
        }

        AuditLogger::log('user.deleted', 'user', $user, 'User soft-deleted');

        $user->delete();

        return back()->with('status', 'User has been removed and can no longer sign in.');
    }

    public function assignRole(Request $request, User $user): RedirectResponse
    {
        abort(403, 'Temporary role assignment is disabled.');

        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $role = Role::findOrFail($validated['role_id']);
        if ($role->slug === 'super_admin') {
            $plan = PlanGate::forUser($request->user());
            if ($plan && User::whereHas('role', fn ($query) => $query->where('slug', 'super_admin'))->count() >= $plan->max_admin_users) {
                return back()->with('status', 'Admin limit reached for your plan.');
            }
        }

        $user->roleAssignments()->create([
            'role_id' => $role->id,
            'assigned_by' => $request->user()?->id,
            'expires_at' => $validated['expires_at'],
        ]);

        return back()->with('status', 'Temporary role assigned.');
    }
}
