<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\PlanGate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneAuditLogs extends Command
{
    protected $signature = 'rbac:prune-audit-logs';
    protected $description = 'Prune audit logs based on plan retention settings.';

    public function handle(): int
    {
        $users = User::with('plan')->get();

        foreach ($users as $user) {
            $days = PlanGate::auditRetentionDays($user);
            $cutoff = now()->subDays($days);

            DB::table('activity_logs')
                ->where('user_id', $user->id)
                ->where('created_at', '<', $cutoff)
                ->delete();
        }

        return self::SUCCESS;
    }
}
