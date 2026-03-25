<?php

namespace Modules\Reader\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Reader\Models\Book;
use Modules\Reader\Models\Chapter;

class ChapterController extends Controller
{
    public function store(Request $request, Book $book): RedirectResponse
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;
        abort_unless($book->organization_id === $orgId, 404);

        $data = $request->validate([
            'number' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:200'],
            'content' => ['nullable', 'string', 'max:200000'],
            'status' => ['nullable', 'in:draft,published'],
        ]);

        $chapter = Chapter::create([
            'book_id' => $book->id,
            'number' => (int) $data['number'],
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ]);

        ActivityLogger::log('reader.chapter_created', $chapter, 'Chapter created');

        return back()->with('status', 'Chapter added.');
    }

    public function update(Request $request, Book $book, Chapter $chapter): RedirectResponse
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;
        abort_unless($book->organization_id === $orgId, 404);
        abort_unless($chapter->book_id === $book->id, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['nullable', 'string', 'max:200000'],
            'status' => ['nullable', 'in:draft,published'],
        ]);

        $chapter->update([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'status' => $data['status'] ?? $chapter->status,
        ]);

        ActivityLogger::log('reader.chapter_updated', $chapter, 'Chapter updated');

        return back()->with('status', 'Chapter updated.');
    }

    public function destroy(Request $request, Book $book, Chapter $chapter): RedirectResponse
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;
        abort_unless($book->organization_id === $orgId, 404);
        abort_unless($chapter->book_id === $book->id, 404);

        $chapter->delete();

        ActivityLogger::log('reader.chapter_deleted', $chapter, 'Chapter deleted');

        return back()->with('status', 'Chapter deleted.');
    }
}

