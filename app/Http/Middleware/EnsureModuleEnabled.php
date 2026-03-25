<?php

namespace App\Http\Middleware;

use App\Models\Module;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        if (! Schema::hasTable('modules')) {
            abort(404);
        }

        $enabled = (bool) Module::where('slug', $slug)->value('enabled');
        if (! $enabled) {
            abort(404);
        }

        return $next($request);
    }
}

