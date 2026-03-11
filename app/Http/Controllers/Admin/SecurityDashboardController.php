<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SecurityDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $recentEvents = ActivityLog::with('user')
            ->orderByDesc('id')
            ->limit(15)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'id' => $log->id,
                'action' => $log->action,
                'email' => $log->user?->email,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at?->toDateTimeString(),
            ]);

        $failedLogins = ActivityLog::where('action', 'login.failed')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'id' => $log->id,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at?->toDateTimeString(),
            ]);

        $activeSessions = DB::table('sessions')
            ->whereNotNull('user_id')
            ->count();

        $recentSessions = DB::table('sessions')
            ->whereNotNull('user_id')
            ->orderByDesc('last_activity')
            ->limit(15)
            ->get()
            ->map(fn ($session) => [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'last_activity' => $session->last_activity,
            ]);

        $tokenUsage = ApiToken::count();
        $adminCount = User::whereHas('role', fn ($query) => $query->where('slug', 'super_admin'))->count();

        return Inertia::render('admin/SecurityDashboard', [
            'recentEvents' => $recentEvents,
            'failedLogins' => $failedLogins,
            'activeSessions' => $activeSessions,
            'recentSessions' => $recentSessions,
            'tokenCount' => $tokenUsage,
            'adminCount' => $adminCount,
        ]);
    }

    public function destroySession(Request $request, string $sessionId): RedirectResponse
    {
        DB::table('sessions')
            ->where('id', $sessionId)
            ->delete();

        return back();
    }
}
