<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;

class PlanGate
{
    public static function forUser(?User $user): ?Plan
    {
        $orgPlan = $user?->currentOrganization?->plan;
        if ($orgPlan) {
            return $orgPlan;
        }

        return $user?->plan ?: Plan::where('slug', 'starter')->first();
    }

    public static function allows(?User $user, string $feature): bool
    {
        $plan = self::forUser($user);

        if (!$plan) {
            return false;
        }

        return match ($feature) {
            'api_tokens' => (bool) $plan->allow_api_tokens,
            'ip_allowlist' => (bool) $plan->allow_ip_allowlist,
            'mfa_enforcement' => (bool) $plan->allow_mfa_enforcement,
            default => false,
        };
    }

    public static function auditRetentionDays(?User $user): int
    {
        return self::forUser($user)?->audit_retention_days ?? 30;
    }
}
