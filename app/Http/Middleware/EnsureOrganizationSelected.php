<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->current_organization_id) {
            return $next($request);
        }

        $firstOrgId = $user->organizations()
            ->wherePivot('status', 'active')
            ->orderBy('organizations.id')
            ->limit(1)
            ->pluck('organizations.id')
            ->first();

        if ($firstOrgId) {
            $user->forceFill(['current_organization_id' => $firstOrgId])->save();
        }

        return $next($request);
    }
}
