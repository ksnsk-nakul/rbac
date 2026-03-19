<?php

namespace App\Listeners;

use App\Models\LoginActivityLog;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class LogLoginActivity
{
    public function handle(object $event): void
    {
        $request = app(Request::class);

        if ($event instanceof Login) {
            $user = $event->user;
            LoginActivityLog::create([
                'user_id' => $user?->id,
                'organization_id' => $user?->current_organization_id,
                'role_id' => $user?->role_id,
                'email' => $user?->email,
                'event' => 'login',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_id' => $request->attributes->get('request_id'),
                'metadata' => [
                    'guard' => $event->guard,
                    'remember' => $event->remember,
                ],
            ]);
            ActivityLogger::log('login', $user, 'User logged in', $user?->id);
        }

        if ($event instanceof Logout) {
            $user = $event->user;
            LoginActivityLog::create([
                'user_id' => $user?->id,
                'organization_id' => $user?->current_organization_id,
                'role_id' => $user?->role_id,
                'email' => $user?->email,
                'event' => 'logout',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_id' => $request->attributes->get('request_id'),
                'metadata' => [
                    'guard' => $event->guard,
                ],
            ]);
            ActivityLogger::log('logout', $user, 'User logged out', $user?->id);
        }

        if ($event instanceof Failed) {
            $email = $event->credentials['email'] ?? null;
            LoginActivityLog::create([
                'user_id' => $event->user?->id,
                'organization_id' => $event->user?->current_organization_id,
                'role_id' => $event->user?->role_id,
                'email' => $email,
                'event' => 'failed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_id' => $request->attributes->get('request_id'),
                'metadata' => [
                    'guard' => $event->guard,
                ],
            ]);
            ActivityLogger::log('login.failed', $event->user, 'Login failed', $event->user?->id);
        }
    }
}
