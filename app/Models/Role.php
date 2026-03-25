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
        'is_protected',
        'mfa_required',
        'require_ip_allowlist',
    ];

    protected function casts(): array
    {
        return [
            'is_subadmin' => 'boolean',
            'is_default' => 'boolean',
            'is_protected' => 'boolean',
            'mfa_required' => 'boolean',
            'require_ip_allowlist' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Role $role): void {
            if ($role->is_protected) {
                throw new \RuntimeException('Protected roles cannot be deleted.');
            }
        });
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
