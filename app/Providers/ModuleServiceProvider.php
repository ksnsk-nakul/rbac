<?php

namespace App\Providers;

use App\Modules\ModuleRegistry;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class, fn () => new ModuleRegistry());
    }

    public function boot(): void
    {
        $registry = $this->app->make(ModuleRegistry::class);
        $registry->syncDatabase();

        $registry->registerPermissions($registry->discover());
        $enabled = $registry->enabled();

        foreach ($enabled as $manifest) {
            if (! $manifest->provider) {
                continue;
            }

            $this->app->register($manifest->provider);
        }
    }
}
