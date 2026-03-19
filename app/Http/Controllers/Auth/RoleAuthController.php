<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Laravel\Fortify\Features;

class RoleAuthController extends Controller
{
    public function login(Request $request, string $role): Response|RedirectResponse
    {
        if ($request->user()) {
            return Inertia::location($this->redirectForAuthenticated($request));
        }

        $roleModel = Role::where('route', $role)->firstOrFail();
        $roles = Role::orderBy('name')->get(['id', 'name', 'slug', 'route', 'is_default']);

        return Inertia::render('auth/RoleLogin', [
            'role' => $roleModel->route,
            'roleName' => $roleModel->name,
            'isDefault' => $roleModel->is_default,
            'roles' => $roles,
            'canRegister' => Features::enabled(Features::registration()),
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
        ]);
    }

    public function register(Request $request, string $role): Response|RedirectResponse
    {
        if ($request->user()) {
            return Inertia::location($this->redirectForAuthenticated($request));
        }

        $roleModel = Role::where('route', $role)->firstOrFail();

        return Inertia::render('auth/RoleRegister', [
            'role' => $roleModel->route,
            'roleName' => $roleModel->name,
        ]);
    }

    private function redirectForAuthenticated(Request $request): string
    {
        $lastVisited = $request->session()->get('last_visited');

        if (is_string($lastVisited) && $lastVisited !== '') {
            return $lastVisited;
        }

        $user = $request->user();

        if ($user && ($user->isAdmin() || $user->isSubadmin())) {
            return '/admin/dashboard';
        }

        return '/dashboard';
    }
}
