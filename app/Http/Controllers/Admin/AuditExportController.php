<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditExportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $format = $request->query('format', 'csv');

        $query = ActivityLog::query()->with('user');

        $user = $request->user();
        if (! $user || ! $user->isAdmin()) {
            // Non-admin roles may only export their own activity logs.
            $query->where('user_id', $user?->id);
        }

        if ($request->filled('user')) {
            // Ignore cross-user exports for non-admins.
            if ($user && $user->isAdmin()) {
                $query->where('user_id', $request->input('user'));
            }
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('ip')) {
            $query->where('ip_address', $request->input('ip'));
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }

        if ($format === 'json') {
            $data = $query->orderByDesc('id')->get()->map(fn (ActivityLog $log) => [
                'id' => $log->id,
                'user_id' => $log->user_id,
                'email' => $log->user?->email,
                'action' => $log->action,
                'description' => $log->description,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at?->toIso8601String(),
            ]);

            return response()->streamDownload(function () use ($data) {
                echo $data->toJson(JSON_PRETTY_PRINT);
            }, 'audit-logs.json', ['Content-Type' => 'application/json']);
        }

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'User ID', 'Email', 'Action', 'Description', 'IP', 'User Agent', 'Created At']);

            $query->orderByDesc('id')->chunk(500, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->user_id,
                        $log->user?->email,
                        $log->action,
                        $log->description,
                        $log->ip_address,
                        $log->user_agent,
                        $log->created_at?->toIso8601String(),
                    ]);
                }
            });

            fclose($handle);
        }, 'audit-logs.csv', ['Content-Type' => 'text/csv']);
    }
}
