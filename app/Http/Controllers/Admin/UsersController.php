<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::with('role')
            ->withTrashed()
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->slug,
                'deleted_at' => $user->deleted_at?->toDateTimeString(),
            ]);

        return Inertia::render('admin/Users', [
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $role = Role::where('slug', 'super_admin')->firstOrFail();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role_id' => $role->id,
        ]);

        ActivityLogger::log('user.created', $user, 'User created');

        return back()->with('status', 'User created.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
        ]);

        $user->update($validated);

        ActivityLogger::log('user.updated', $user, 'User updated');

        return back()->with('status', 'User updated.');
    }

    public function ban(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            return back()->with('status', 'You cannot ban your own account.');
        }

        if (! $user->trashed()) {
            $user->delete();
            ActivityLogger::log('user.banned', $user, 'User banned');
        }

        return back()->with('status', 'User banned.');
    }

    public function restore(Request $request, int $user): RedirectResponse
    {
        $account = User::withTrashed()->findOrFail($user);

        if ($account->trashed()) {
            $account->restore();
            ActivityLogger::log('user.restored', $account, 'User restored');
        }

        return back()->with('status', 'User restored.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            return back()->with('status', 'You cannot delete your own account.');
        }

        $user->forceDelete();

        ActivityLogger::log('user.deleted', $user, 'User deleted');

        return back()->with('status', 'User deleted.');
    }
}
