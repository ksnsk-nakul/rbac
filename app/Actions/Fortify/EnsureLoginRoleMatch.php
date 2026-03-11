<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EnsureLoginRoleMatch
{
    /**
     * Ensure the authenticated user has the role required by the login portal.
     * Run after AttemptToAuthenticate in the login pipeline.
     */
    public function __invoke(Request $request, $next): mixed
    {
        $intendedRole = $request->input('intended_role');

        if (! $intendedRole) {
            return $next($request);
        }

        /** @var User|null $user */
        $user = $request->user();

        if (! $user || ! $user->role) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            throw ValidationException::withMessages([
                'email' => ['You do not have access to this login.'],
            ]);
        }

        $role = Role::where('route', $intendedRole)->orWhere('slug', $intendedRole)->first();

        if (! $role || $user->role->id !== $role->id) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            throw ValidationException::withMessages([
                'email' => ['You do not have access to this login.'],
            ]);
        }

        return $next($request);
    }
}
