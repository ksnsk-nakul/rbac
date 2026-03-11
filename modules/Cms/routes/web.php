<?php

use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\CmsController;

Route::middleware(['web', 'auth', 'verified', 'ensure.not.deleted', 'permission:cms.view'])
    ->prefix('admin/cms')
    ->name('modules.cms.')
    ->group(function () {
        Route::get('/', [CmsController::class, 'index'])->name('index');
    });
