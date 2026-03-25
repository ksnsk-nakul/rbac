<?php

namespace Modules\Reader\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Reader\Models\Book;
use Modules\Reader\Models\Chapter;

class TextImportService
{
    public function importTxtToChapter(Book $book, string $disk, string $path): void
    {
        $raw = (string) Storage::disk($disk)->get($path);
        $raw = trim($raw);
        if ($raw === '') {
            return;
        }

        $escaped = e($raw);
        $html = '<p>'.str_replace("\n", "</p>\n<p>", $escaped).'</p>';

        $nextNumber = (int) (Chapter::where('book_id', $book->id)->max('number') ?? 0) + 1;

        Chapter::create([
            'book_id' => $book->id,
            'number' => $nextNumber,
            'title' => 'Imported text',
            'content' => $html,
            'status' => 'draft',
        ]);
    }
}

