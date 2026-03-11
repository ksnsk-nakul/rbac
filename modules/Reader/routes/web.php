<?php

use Illuminate\Support\Facades\Route;
use Modules\Reader\Http\Controllers\ReaderController;

Route::middleware(['web', 'auth', 'verified', 'ensure.not.deleted', 'permission:reader.view'])
    ->prefix('admin/reader')
    ->name('modules.reader.')
    ->group(function () {
        Route::get('/', [ReaderController::class, 'index'])->name('index');
    });
