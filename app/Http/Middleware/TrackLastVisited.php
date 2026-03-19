<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLastVisited
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->user()) {
            return $response;
        }

        if (! $request->isMethod('GET') || $request->expectsJson()) {
            return $response;
        }

        if ($this->shouldSkip($request)) {
            return $response;
        }

        $request->session()->put('last_visited', $request->fullUrl());

        return $response;
    }

    private function shouldSkip(Request $request): bool
    {
        if ($request->routeIs(
            'home',
            'role.login',
            'role.register',
            'login',
            'register',
            'logout',
            'password.request',
            'password.reset',
            'two-factor.login',
            'verification.notice'
        )) {
            return true;
        }

        return (bool) preg_match('#^/(login|register|logout|password|two-factor|up)(/|$)#', $request->path());
    }
}
