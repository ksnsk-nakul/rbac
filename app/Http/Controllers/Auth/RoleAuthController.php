<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class RoleAuthController extends Controller
{
    public function login(Request $request, string $role): Response
    {
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

    public function register(Request $request, string $role): Response
    {
        $roleModel = Role::where('route', $role)->firstOrFail();

        return Inertia::render('auth/RoleRegister', [
            'role' => $roleModel->route,
            'roleName' => $roleModel->name,
        ]);
    }
}
