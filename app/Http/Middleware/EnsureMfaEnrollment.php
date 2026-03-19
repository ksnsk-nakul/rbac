<?php

namespace App\Http\Middleware;

use App\Services\DeviceFingerprint;
use App\Services\PlanGate;
use Closure;
use Illuminate\Http\Request;

class EnsureMfaEnrollment
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

        if ($role && $role->mfa_required && PlanGate::allows($user, 'mfa_enforcement')) {
            $fingerprint = DeviceFingerprint::fromRequest($request, $user->id);
            $trustedDevice = $user->trustedDevices()->where('device_fingerprint', $fingerprint)->first();
            $trusted = (bool) $trustedDevice;

            if ($trusted) {
                $trustedDevice?->update(['last_used_at' => now()]);
                return $next($request);
            }

            if (!$user->two_factor_confirmed_at || !$user->two_factor_secret) {
                return redirect('/account/settings/security');
            }
        }

        return $next($request);
    }
}
