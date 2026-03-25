<?php

use Illuminate\Support\Facades\Route;
use Modules\Reader\Http\Controllers\Admin\ApprovalsController as AdminApprovalsController;
use Modules\Reader\Http\Controllers\BookController;
use Modules\Reader\Http\Controllers\ChapterController;
use Modules\Reader\Http\Controllers\FileController;

Route::middleware([
    'auth',
    'verified',
    'ensure.not.deleted',
    'ensure.mfa',
    'ensure.ip_allowlist',
    'ensure.org',
    'ensure.license',
    'ensure.module:reader',
])->prefix('reader')->name('reader.')->group(function (): void {
    Route::get('books', [BookController::class, 'index'])
        ->middleware('permission:reader.books.view')
        ->name('books.index');

    Route::get('books/create', [BookController::class, 'create'])
        ->middleware('permission:reader.books.create')
        ->name('books.create');
    Route::post('books', [BookController::class, 'store'])
        ->middleware('permission:reader.books.create')
        ->name('books.store');

    Route::get('books/{book}', [BookController::class, 'show'])
        ->middleware('permission:reader.read')
        ->name('books.show');

    Route::get('books/{book}/edit', [BookController::class, 'edit'])
        ->middleware('permission:reader.books.edit')
        ->name('books.edit');
    Route::put('books/{book}', [BookController::class, 'update'])
        ->middleware('permission:reader.books.edit')
        ->name('books.update');
    Route::delete('books/{book}', [BookController::class, 'destroy'])
        ->middleware('permission:reader.books.delete')
        ->name('books.destroy');

    Route::post('books/{book}/submit', [BookController::class, 'submitForApproval'])
        ->middleware('permission:reader.approval.submit')
        ->name('books.submit');

    Route::post('books/{book}/files', [FileController::class, 'store'])
        ->middleware('permission:reader.books.edit')
        ->name('books.files.store');
    Route::get('books/{book}/files/{file}', [FileController::class, 'download'])
        ->middleware('permission:reader.read')
        ->name('books.files.download');

    Route::post('books/{book}/chapters', [ChapterController::class, 'store'])
        ->middleware('permission:reader.chapters.manage')
        ->name('chapters.store');
    Route::put('books/{book}/chapters/{chapter}', [ChapterController::class, 'update'])
        ->middleware('permission:reader.chapters.manage')
        ->name('chapters.update');
    Route::delete('books/{book}/chapters/{chapter}', [ChapterController::class, 'destroy'])
        ->middleware('permission:reader.chapters.manage')
        ->name('chapters.destroy');
});

Route::middleware([
    'auth',
    'verified',
    'ensure.not.deleted',
    'ensure.mfa',
    'ensure.ip_allowlist',
    'ensure.org',
    'ensure.license',
    'ensure.module:reader',
])->prefix('admin/reader')->name('admin.reader.')->group(function (): void {
    Route::get('approvals', [AdminApprovalsController::class, 'index'])
        ->middleware('permission:reader.approvals.view')
        ->name('approvals.index');
    Route::post('approvals/{book}/approve', [AdminApprovalsController::class, 'approve'])
        ->middleware('permission:reader.approvals.manage')
        ->name('approvals.approve');
    Route::post('approvals/{book}/reject', [AdminApprovalsController::class, 'reject'])
        ->middleware('permission:reader.approvals.manage')
        ->name('approvals.reject');
    Route::post('approvals/{book}/copyright', [AdminApprovalsController::class, 'markCopyrighted'])
        ->middleware('permission:reader.approvals.manage')
        ->name('approvals.copyright');
});
