<?php

namespace Modules\Reader\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chapter extends Model
{
    protected $table = 'reader_chapters';

    protected $fillable = [
        'book_id',
        'number',
        'title',
        'content',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'book_id' => 'integer',
            'number' => 'integer',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}

