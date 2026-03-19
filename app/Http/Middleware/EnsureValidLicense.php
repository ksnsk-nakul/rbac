<?php

namespace App\Http\Middleware;

use App\Services\Licensing\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isProduction()) {
            return $next($request);
        }

        if (! filter_var(env('LICENSE_ENFORCE', false), FILTER_VALIDATE_BOOL)) {
            return $next($request);
        }

        $instanceId = sha1((string) config('app.key')."|".(string) config('app.url'));

        $service = app(LicenseService::class);
        $valid = $service->validateAndTouch(null, $instanceId, config('app.url'));

        if (! $valid) {
            abort(403, 'Invalid license.');
        }

        return $next($request);
    }
}
