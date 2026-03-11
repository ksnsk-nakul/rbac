<?php

namespace Modules\Reader;

use Illuminate\Support\ServiceProvider;

class ReaderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'reader');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
