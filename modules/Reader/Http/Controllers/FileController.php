<?php

namespace Modules\Reader\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Modules\Reader\Models\Book;
use Modules\Reader\Models\BookFile;
use Modules\Reader\Services\TextImportService;

class FileController extends Controller
{
    public function store(Request $request, Book $book, TextImportService $importer)
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;
        abort_unless($book->organization_id === $orgId, 404);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:51200'], // 50MB
        ]);

        $file = $data['file'];
        $original = $file->getClientOriginalName();
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $format = in_array($ext, ['pdf', 'doc', 'docx', 'epub', 'txt'], true) ? $ext : null;

        $disk = 'local';
        $path = $file->storeAs(
            'reader/books/'.$book->id,
            now()->format('Ymd_His').'_'.preg_replace('/[^A-Za-z0-9._-]/', '_', $original),
            $disk
        );

        $record = BookFile::create([
            'book_id' => $book->id,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $original,
            'mime' => $file->getClientMimeType(),
            'size' => (int) $file->getSize(),
            'format' => $format,
        ]);

        // Minimal MVP import: auto-create a chapter from TXT uploads.
        if ($format === 'txt') {
            $importer->importTxtToChapter($book, $disk, $path);
        }

        ActivityLogger::log('reader.file_uploaded', $record, 'Book file uploaded');

        return back()->with('status', 'File uploaded.');
    }

    public function download(Request $request, Book $book, BookFile $file): StreamedResponse
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;
        abort_unless($book->organization_id === $orgId, 404);
        abort_unless($file->book_id === $book->id, 404);

        // Only allow downloads for approved books unless user can edit/manage books.
        if ($book->status !== 'approved' && ! $user->hasPermission('reader.books.edit') && ! $user->isAdmin()) {
            abort(403);
        }

        $disk = $file->disk ?: 'local';
        abort_unless(Storage::disk($disk)->exists($file->path), 404);

        return Storage::disk($disk)->download($file->path, $file->original_name ?: basename($file->path));
    }
}

