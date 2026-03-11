<?php

use Illuminate\Support\Facades\Route;
use Modules\Tasks\Http\Controllers\TasksController;

Route::middleware(['web', 'auth', 'verified', 'ensure.not.deleted', 'permission:tasks.view'])
    ->prefix('admin/tasks')
    ->name('modules.tasks.')
    ->group(function () {
        Route::get('/', [TasksController::class, 'index'])->name('index');
    });
