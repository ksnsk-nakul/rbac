<?php

namespace Modules\Reader\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $table = 'reader_books';

    protected $fillable = [
        'organization_id',
        'author_id',
        'title',
        'slug',
        'description',
        'status',
        'is_copyrighted',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'organization_id' => 'integer',
            'author_id' => 'integer',
            'is_copyrighted' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class, 'book_id')->orderBy('number');
    }

    public function files(): HasMany
    {
        return $this->hasMany(BookFile::class, 'book_id');
    }

    public function metadata(): HasMany
    {
        return $this->hasMany(BookMetadata::class, 'book_id');
    }
}

