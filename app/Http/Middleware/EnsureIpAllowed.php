<?php

namespace App\Http\Middleware;

use App\Services\PlanGate;
use Closure;
use Illuminate\Http\Request;

class EnsureIpAllowed
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($request->is('account/settings/security') || $request->is('account/settings/security/*')) {
            return $next($request);
        }

        $role = $user->currentRole();

        if ($role && $role->require_ip_allowlist) {
            if (!PlanGate::allows($user, 'ip_allowlist')) {
                return $next($request);
            }

            $allowed = $role->ipAllowlistEntries()
                ->where('ip_address', $request->ip())
                ->exists();

            if (!$allowed) {
                abort(403, 'Access denied from this IP address.');
            }
        }

        return $next($request);
    }
}
