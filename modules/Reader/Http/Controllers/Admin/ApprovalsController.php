<?php

namespace Modules\Reader\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Reader\Models\Book;

class ApprovalsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;

        $status = $request->string('status')->toString();
        $status = $status !== '' ? $status : 'submitted';

        $books = Book::query()
            ->with('author')
            ->where('organization_id', $orgId)
            ->where('status', $status)
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->map(fn (Book $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'slug' => $b->slug,
                'status' => $b->status,
                'is_copyrighted' => (bool) $b->is_copyrighted,
                'author' => $b->author ? [
                    'id' => $b->author->id,
                    'name' => $b->author->name,
                ] : null,
                'created_at' => $b->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/reader/Approvals', [
            'books' => $books,
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    public function approve(Request $request, Book $book): RedirectResponse
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;
        abort_unless($book->organization_id === $orgId, 404);

        $book->forceFill([
            'status' => 'approved',
            'published_at' => $book->published_at ?: now(),
        ])->save();

        ActivityLogger::log('reader.book_approved', $book, 'Book approved');

        return back()->with('status', 'Book approved.');
    }

    public function reject(Request $request, Book $book): RedirectResponse
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;
        abort_unless($book->organization_id === $orgId, 404);

        $book->forceFill([
            'status' => 'rejected',
        ])->save();

        ActivityLogger::log('reader.book_rejected', $book, 'Book rejected');

        return back()->with('status', 'Book rejected.');
    }

    public function markCopyrighted(Request $request, Book $book): RedirectResponse
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;
        abort_unless($book->organization_id === $orgId, 404);

        $book->forceFill(['is_copyrighted' => true])->save();
        ActivityLogger::log('reader.book_copyrighted', $book, 'Book marked as copyrighted');

        return back()->with('status', 'Copyright flag applied.');
    }
}

