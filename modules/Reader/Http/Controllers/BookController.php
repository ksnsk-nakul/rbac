<?php

namespace Modules\Reader\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Reader\Models\Author;
use Modules\Reader\Models\Book;
use Modules\Reader\Models\BookFile;

class BookController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;

        $books = Book::query()
            ->with('author')
            ->where('organization_id', $orgId)
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

        return Inertia::render('reader/Books', [
            'books' => $books,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('reader/BookEdit', [
            'book' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $author = Author::firstOrCreate(
            ['organization_id' => $orgId, 'user_id' => $user->id],
            ['name' => $user->name, 'status' => 'active']
        );

        $slug = Str::slug($data['title']);
        if ($slug === '') {
            $slug = 'book-'.Str::lower(Str::random(6));
        }

        // Ensure unique slug per org.
        $base = $slug;
        $i = 2;
        while (Book::where('organization_id', $orgId)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        $book = Book::create([
            'organization_id' => $orgId,
            'author_id' => $author->id,
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'status' => 'draft',
        ]);

        ActivityLogger::log('reader.book_created', $book, 'Book created');

        return redirect()->route('reader.books.edit', $book);
    }

    public function show(Request $request, Book $book): Response
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;
        abort_unless($book->organization_id === $orgId, 404);

        $book->load(['author', 'chapters', 'files']);

        return Inertia::render('reader/Reader', [
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'description' => $book->description,
                'status' => $book->status,
                'is_copyrighted' => (bool) $book->is_copyrighted,
                'author' => $book->author ? ['id' => $book->author->id, 'name' => $book->author->name] : null,
            ],
            'chapters' => $book->chapters->map(fn ($c) => [
                'id' => $c->id,
                'number' => $c->number,
                'title' => $c->title,
                'content' => $c->content,
                'status' => $c->status,
            ]),
            'files' => $book->files->map(fn (BookFile $f) => [
                'id' => $f->id,
                'original_name' => $f->original_name,
                'format' => $f->format,
                'size' => $f->size,
                'download_url' => "/reader/books/{$book->id}/files/{$f->id}",
            ]),
        ]);
    }

    public function edit(Request $request, Book $book): Response
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;
        abort_unless($book->organization_id === $orgId, 404);

        $book->load(['chapters', 'files']);

        return Inertia::render('reader/BookEdit', [
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'description' => $book->description,
                'status' => $book->status,
                'chapters' => $book->chapters->map(fn ($c) => [
                    'id' => $c->id,
                    'number' => $c->number,
                    'title' => $c->title,
                    'status' => $c->status,
                ]),
                'files' => $book->files->map(fn (BookFile $f) => [
                    'id' => $f->id,
                    'original_name' => $f->original_name,
                    'format' => $f->format,
                    'size' => $f->size,
                    'download_url' => "/reader/books/{$book->id}/files/{$f->id}",
                ]),
            ],
        ]);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;
        abort_unless($book->organization_id === $orgId, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $book->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        ActivityLogger::log('reader.book_updated', $book, 'Book updated');

        return back()->with('status', 'Book updated.');
    }

    public function submitForApproval(Request $request, Book $book): RedirectResponse
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;
        abort_unless($book->organization_id === $orgId, 404);

        if ($book->status === 'draft' || $book->status === 'rejected') {
            $book->forceFill(['status' => 'submitted'])->save();
            ActivityLogger::log('reader.book_submitted', $book, 'Book submitted for approval');
        }

        return back()->with('status', 'Submitted for approval.');
    }

    public function destroy(Request $request, Book $book): RedirectResponse
    {
        $user = $request->user();
        $orgId = (int) $user->current_organization_id;
        abort_unless($book->organization_id === $orgId, 404);

        $book->delete();

        ActivityLogger::log('reader.book_deleted', $book, 'Book deleted');

        return redirect()->route('reader.books.index')->with('status', 'Book deleted.');
    }
}
