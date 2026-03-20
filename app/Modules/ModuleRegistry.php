<?php

namespace App\Modules;

use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ModuleRegistry
{
    /**
     * @return ModuleManifest[]
     */
    public function discover(): array
    {
        $basePath = base_path('modules');
        if (! File::exists($basePath)) {
            return [];
        }

        $manifests = [];
        foreach (File::directories($basePath) as $moduleDir) {
            $manifestPath = $moduleDir.DIRECTORY_SEPARATOR.'module.json';
            if (! File::exists($manifestPath)) {
                continue;
            }

            $raw = json_decode(File::get($manifestPath), true);
            if (! is_array($raw)) {
                continue;
            }

            $manifests[] = new ModuleManifest(
                name: (string) ($raw['name'] ?? basename($moduleDir)),
                slug: (string) ($raw['slug'] ?? strtolower(basename($moduleDir))),
                directory: basename($moduleDir),
                version: $raw['version'] ?? null,
                description: $raw['description'] ?? null,
                provider: $raw['provider'] ?? null,
                permissions: $raw['permissions'] ?? [],
                navigation: $raw['navigation'] ?? [],
                defaultEnabled: (bool) ($raw['default_enabled'] ?? false),
                allowedPlans: $this->normalizeAllowedPlans($raw),
                requiresApiKey: (bool) ($raw['requires_api_key'] ?? false),
            );
        }

        return $manifests;
    }

    private function normalizeAllowedPlans(array $raw): ?array
    {
        if (isset($raw['allowed_plans']) && is_array($raw['allowed_plans'])) {
            $plans = array_values(array_filter(array_map('strval', $raw['allowed_plans'])));
            return $plans !== [] ? $plans : null;
        }

        if (isset($raw['min_plan']) && is_string($raw['min_plan']) && $raw['min_plan'] !== '') {
            // Backward-compatible helper. If you use min_plan, list all plans at/above it in your app logic.
            return [$raw['min_plan']];
        }

        return null;
    }

    /**
     * Sync discovered modules into the database.
     *
     * @return ModuleManifest[]
     */
    public function syncDatabase(): array
    {
        $manifests = $this->discover();

        if (! Schema::hasTable('modules')) {
            return $manifests;
        }

        foreach ($manifests as $manifest) {
            Module::query()->updateOrCreate(
                ['slug' => $manifest->slug],
                [
                    'name' => $manifest->name,
                    'version' => $manifest->version,
                    'description' => $manifest->description,
                    'allowed_plans' => $manifest->allowedPlans,
                    'requires_api_key' => (bool) $manifest->requiresApiKey,
                    'enabled' => Module::where('slug', $manifest->slug)->value('enabled')
                        ?? $manifest->defaultEnabled,
                ],
            );
        }

        return $manifests;
    }

    /**
     * @return ModuleManifest[]
     */
    public function enabled(): array
    {
        $manifests = $this->discover();

        if (! Schema::hasTable('modules')) {
            return $manifests;
        }

        $enabled = Module::where('enabled', true)->pluck('slug')->all();

        return array_values(array_filter(
            $manifests,
            fn (ModuleManifest $manifest) => in_array($manifest->slug, $enabled, true),
        ));
    }

    public function registerPermissions(array $manifests): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $permissionIds = [];

        foreach ($manifests as $manifest) {
            foreach ($manifest->permissions as $permission) {
                if (! isset($permission['slug'])) {
                    continue;
                }

                $record = Permission::firstOrCreate(
                    ['slug' => $permission['slug']],
                    [
                        'name' => $permission['name'] ?? $permission['slug'],
                        'main_group' => $permission['group'] ?? $manifest->slug,
                    ],
                );

                $permissionIds[] = $record->id;
            }
        }

        if (! Schema::hasTable('roles')) {
            return;
        }

        $role = Role::where('slug', 'super_admin')->first();
        if ($role && $permissionIds !== []) {
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
    }

    public function navigation(array $manifests): array
    {
        $items = [];

        foreach ($manifests as $manifest) {
            foreach ($manifest->navigation as $item) {
                if (! isset($item['title'], $item['href'])) {
                    continue;
                }

                $items[] = [
                    'title' => $item['title'],
                    'href' => $item['href'],
                    'permission' => $item['permission'] ?? null,
                ];
            }
        }

        return $items;
    }
}
