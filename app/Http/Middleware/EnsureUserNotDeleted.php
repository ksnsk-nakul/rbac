<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserNotDeleted
{
    /**
     * If the authenticated user was soft-deleted, log them out.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->trashed()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('status', 'Your account is no longer available.');
        }

        return $next($request);
    }
}
