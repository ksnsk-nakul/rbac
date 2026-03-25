<?php

namespace Modules\Reader\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookFile extends Model
{
    protected $table = 'reader_book_files';

    protected $fillable = [
        'book_id',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
        'format',
    ];

    protected function casts(): array
    {
        return [
            'book_id' => 'integer',
            'size' => 'integer',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}

