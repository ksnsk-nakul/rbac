<?php

use Illuminate\Support\Facades\Route;
use Modules\Support\Http\Controllers\SupportController;

Route::middleware(['web', 'auth', 'verified', 'ensure.not.deleted', 'permission:support.view'])
    ->prefix('admin/support')
    ->name('modules.support.')
    ->group(function () {
        Route::get('/', [SupportController::class, 'index'])->name('index');
    });
