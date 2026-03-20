<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'owner_user_id',
        'plan_id',
        'billing_provider',
        'razorpay_customer_id',
        'addon_api_key_hash',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(OrganizationUser::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_users')
            ->withPivot(['org_role', 'status', 'invited_by_user_id', 'invitation_token', 'invitation_expires_at'])
            ->withTimestamps();
    }
}
