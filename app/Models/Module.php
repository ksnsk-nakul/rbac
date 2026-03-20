<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'enabled',
        'version',
        'description',
        'allowed_plans',
        'requires_api_key',
        'api_key_hash',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'allowed_plans' => 'array',
            'requires_api_key' => 'boolean',
        ];
    }
}
