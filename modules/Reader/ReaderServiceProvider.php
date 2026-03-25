<?php

namespace Modules\Reader;

use Modules\Reader\Services\TextImportService;
use Illuminate\Support\ServiceProvider;

class ReaderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TextImportService::class, fn () => new TextImportService());
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }
}
