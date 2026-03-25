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
        try {
            $registry->syncDatabase();
            $registry->registerPermissions($registry->discover());
            $registry->registerRoles($registry->discover());
            $enabled = $registry->enabled();
        } catch (\Throwable $e) {
            if ($this->app->runningInConsole()) {
                return;
            }

            throw $e;
        }

        foreach ($enabled as $manifest) {
            if (! $manifest->provider) {
                continue;
            }

            $this->app->register($manifest->provider);
        }
    }
}
