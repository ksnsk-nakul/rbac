<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'main_group', 'is_protected'];

    protected function casts(): array
    {
        return [
            'is_protected' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Permission $permission): void {
            if ($permission->is_protected) {
                throw new \RuntimeException('Protected permissions cannot be deleted.');
            }
        });
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
