<?php

namespace Modules\Reader\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookMetadata extends Model
{
    protected $table = 'reader_book_metadata';

    protected $fillable = [
        'book_id',
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'book_id' => 'integer',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}

