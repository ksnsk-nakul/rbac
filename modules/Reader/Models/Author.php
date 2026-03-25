<?php

namespace Modules\Reader\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    protected $table = 'reader_authors';

    protected $fillable = [
        'organization_id',
        'user_id',
        'name',
        'bio',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'organization_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'author_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}

