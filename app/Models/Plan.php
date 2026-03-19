<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'currency',
        'price_monthly',
        'price_yearly',
        'razorpay_plan_id_monthly',
        'razorpay_plan_id_yearly',
        'is_active',
        'audit_retention_days',
        'max_admin_users',
        'allow_api_tokens',
        'allow_ip_allowlist',
        'allow_mfa_enforcement',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'allow_api_tokens' => 'boolean',
            'allow_ip_allowlist' => 'boolean',
            'allow_mfa_enforcement' => 'boolean',
        ];
    }
}
