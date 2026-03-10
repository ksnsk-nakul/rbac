<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdminOrSubadmin
{
    /**
     * Restrict route to users with admin or subadmin role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->isAdmin() && ! $user->isSubadmin())) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
