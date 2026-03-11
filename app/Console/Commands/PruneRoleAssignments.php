<?php

namespace App\Console\Commands;

use App\Models\UserRoleAssignment;
use Illuminate\Console\Command;

class PruneRoleAssignments extends Command
{
    protected $signature = 'rbac:prune-role-assignments';
    protected $description = 'Remove expired temporary role assignments.';

    public function handle(): int
    {
        UserRoleAssignment::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        return self::SUCCESS;
    }
}
