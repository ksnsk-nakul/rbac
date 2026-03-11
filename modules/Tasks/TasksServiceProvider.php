<?php

namespace Modules\Tasks;

use Illuminate\Support\ServiceProvider;

class TasksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'tasks');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
