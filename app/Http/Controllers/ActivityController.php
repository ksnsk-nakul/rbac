<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    public function self(Request $request): Response
    {
        $user = $request->user();

        $logs = ActivityLog::with('user')
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (ActivityLog $log) => [
                'id' => $log->id,
                'email' => $log->user?->email,
                'event' => $log->action,
                'role' => $log->user?->role?->slug,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('activity/Self', [
            'logs' => $logs,
        ]);
    }

    public function all(Request $request): Response
    {
        $logs = ActivityLog::with('user')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString()
            ->through(fn (ActivityLog $log) => [
                'id' => $log->id,
                'email' => $log->user?->email,
                'event' => $log->action,
                'role' => $log->user?->role?->slug,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('activity/All', [
            'logs' => $logs,
        ]);
    }
}
