<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ManagementController extends Controller
{
    /**
     * List users (library/reader role).
     */
    public function users(Request $request): Response
    {
        $role = Role::where('slug', 'user')->firstOrFail();
        $users = User::with('role')
            ->where('role_id', $role->id)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/management/Users', [
            'users' => $users,
            'roleLabel' => 'Users',
        ]);
    }

    /**
     * List subadmins (dynamically managed).
     */
    public function subadmins(Request $request): Response
    {
        $role = Role::where('slug', 'subadmin')->firstOrFail();
        $users = User::with('role')
            ->where('role_id', $role->id)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/management/Subadmins', [
            'users' => $users,
            'roleLabel' => 'Subadmins',
        ]);
    }

    /**
     * Soft-delete a user so they cannot login or register again.
     * Only non-admin users can be deleted.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            abort(403, 'Cannot remove an admin.');
        }

        if ($request->user()?->isSubadmin() && $user->isSubadmin()) {
            abort(403, 'Subadmins cannot remove other subadmins.');
        }

        $user->delete();

        return back()->with('status', 'User has been removed and can no longer sign in.');
    }

    /**
     * Store a new subadmin (dynamically added under management).
     */
    public function storeSubadmin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
                function (string $attr, mixed $value, \Closure $fail) {
                    if (User::withTrashed()->where('email', $value)->exists()) {
                        $fail('This email cannot be used.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $role = Role::where('slug', 'subadmin')->firstOrFail();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role_id' => $role->id,
        ]);

        return back()->with('status', 'Subadmin added. They can sign in at the subadmin login.');
    }
}
