<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_subadmin',
        'route',
        'is_default',
        'mfa_required',
        'require_ip_allowlist',
    ];

    protected function casts(): array
    {
        return [
            'is_subadmin' => 'boolean',
            'is_default' => 'boolean',
            'mfa_required' => 'boolean',
            'require_ip_allowlist' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function ipAllowlistEntries(): HasMany
    {
        return $this->hasMany(IpAllowlistEntry::class);
    }

    public function isAdmin(): bool
    {
        return $this->slug === 'super_admin';
    }

    public function isUser(): bool
    {
        return $this->slug === 'user';
    }

    public function isSubadmin(): bool
    {
        return $this->slug === 'subadmin' || $this->is_subadmin;
    }

    public function hasPermission(string $slug): bool
    {
        return $this->permissions->contains('slug', $slug);
    }
}
