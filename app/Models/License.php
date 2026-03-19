<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class License extends Model
{
    protected $fillable = [
        'key',
        'customer_email',
        'status',
        'valid_until',
        'max_instances',
    ];

    protected function casts(): array
    {
        return [
            'valid_until' => 'datetime',
        ];
    }

    public function instances(): HasMany
    {
        return $this->hasMany(LicenseInstance::class);
    }
}
