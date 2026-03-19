<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneAuditLogs extends Command
{
    protected $signature = 'rbac:prune-audit-logs';
    protected $description = 'Prune audit logs based on plan retention settings.';

    public function handle(): int
    {
        $orgs = Organization::with('plan')->get();

        foreach ($orgs as $org) {
            $days = (int) ($org->plan?->audit_retention_days ?? 30);
            $cutoff = now()->subDays($days);

            DB::table('activity_logs')
                ->where('organization_id', $org->id)
                ->where('created_at', '<', $cutoff)
                ->delete();
        }

        return self::SUCCESS;
    }
}
