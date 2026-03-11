<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Models\TrustedDevice;
use App\Services\ActivityLogger;
use App\Services\DeviceFingerprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SecurityController extends Controller
{
    public function edit(): Response
    {
        $user = request()->user();

        return Inertia::render('account/settings/Security', [
            'twoFactorEnabled' => (bool) $user?->two_factor_confirmed_at,
            'trustedDevices' => $user?->trustedDevices()
                ->orderByDesc('last_used_at')
                ->get(['id', 'label', 'last_used_at', 'created_at'])
                ->map(fn (TrustedDevice $device) => [
                    'id' => $device->id,
                    'label' => $device->label ?? 'Trusted device',
                    'last_used_at' => $device->last_used_at?->toDateTimeString(),
                    'created_at' => $device->created_at?->toDateTimeString(),
                ]),
        ]);
    }

    public function updatePassword(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->password,
        ]);

        ActivityLogger::log('password.changed', $request->user(), 'Password updated', $request->user()->id);

        return back();
    }

    public function logoutOtherSessions(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        Auth::logoutOtherDevices($validated['current_password']);

        ActivityLogger::log('sessions.logout_other', $request->user(), 'Logged out other sessions', $request->user()->id);

        return back();
    }

    public function storeTrustedDevice(Request $request): RedirectResponse
    {
        $user = $request->user();
        $fingerprint = DeviceFingerprint::fromRequest($request, $user->id);

        $user->trustedDevices()->firstOrCreate(
            ['device_fingerprint' => $fingerprint],
            ['label' => $request->input('label', 'Trusted device'), 'last_used_at' => now()]
        );

        ActivityLogger::log('security.device_trusted', $user, 'Trusted device added', $user->id);

        return back();
    }

    public function destroyTrustedDevice(Request $request, TrustedDevice $device): RedirectResponse
    {
        if ($device->user_id !== $request->user()->id) {
            abort(403);
        }

        $device->delete();

        ActivityLogger::log('security.device_revoked', $request->user(), 'Trusted device removed', $request->user()->id);

        return back();
    }
}
