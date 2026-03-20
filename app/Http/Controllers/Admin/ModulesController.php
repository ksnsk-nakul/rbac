<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Module;
use App\Modules\ModuleRegistry;
use App\Services\ActivityLogger;
use App\Services\PlanGate;
use App\Services\Modules\AddonInstaller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ModulesController extends Controller
{
    public function index(Request $request): Response
    {
        if (! Schema::hasTable('modules')) {
            return Inertia::render('admin/Modules', [
                'modules' => [],
                'planSlug' => null,
                'hasAddonKey' => false,
            ]);
        }

        $user = $request->user();
        $planSlug = PlanGate::forUser($user)?->slug;
        $org = $user?->currentOrganization;

        $registry = app(ModuleRegistry::class);
        $manifests = collect($registry->discover())->keyBy(fn ($m) => $m->slug);

        $modules = Module::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Module $module) => [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
                'enabled' => $module->enabled,
                'version' => $module->version,
                'description' => $module->description,
                'allowed_plans' => $module->allowed_plans,
                'requires_api_key' => (bool) $module->requires_api_key,
                'is_addon' => (function () use ($module, $manifests) {
                    $manifest = $manifests->get($module->slug);
                    if (! $manifest) {
                        return false;
                    }
                    $marker = base_path('modules'.DIRECTORY_SEPARATOR.$manifest->directory.DIRECTORY_SEPARATOR.'.addon-installed');
                    return File::exists($marker);
                })(),
            ]);

        return Inertia::render('admin/Modules', [
            'modules' => $modules,
            'planSlug' => $planSlug,
            'hasAddonKey' => (bool) ($org?->addon_api_key_hash),
        ]);
    }

    public function update(Request $request, Module $module): RedirectResponse
    {
        if (! Schema::hasTable('modules')) {
            abort(503, 'Modules table is missing.');
        }

        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'api_key' => ['nullable', 'string', 'max:200'],
        ]);

        $user = $request->user();
        $org = $user?->currentOrganization;
        $enable = (bool) $validated['enabled'];

        if ($enable) {
            // Subscription gating
            if (! filter_var(env('FEATURES_ALL', false), FILTER_VALIDATE_BOOL)) {
                $allowedPlans = $module->allowed_plans ?: null;
                if (is_array($allowedPlans) && $allowedPlans !== []) {
                    $planSlug = PlanGate::forUser($user)?->slug;
                    abort_unless($planSlug && in_array($planSlug, $allowedPlans, true), 403, 'Your plan does not allow enabling this add-on.');
                }
            }

            // Add-on API key gating
            if ($module->requires_api_key) {
                abort_unless($org instanceof Organization, 403, 'Organization is required.');
                abort_if(! $org->addon_api_key_hash, 422, 'Generate an Add-on API key first.');

                $apiKey = (string) ($validated['api_key'] ?? '');
                abort_if($apiKey === '', 422, 'Add-on API key is required to enable this module.');

                $hash = hash('sha256', $apiKey);
                abort_unless(hash_equals($org->addon_api_key_hash, $hash), 403, 'Invalid Add-on API key.');

                $module->forceFill(['api_key_hash' => $hash]);
            }
        }

        $module->update(['enabled' => $validated['enabled']]);

        ActivityLogger::log(
            $module->enabled ? 'module.enabled' : 'module.disabled',
            $module,
            $module->enabled ? 'Module enabled' : 'Module disabled',
        );

        return back()->with('status', 'Module updated.');
    }

    public function install(Request $request, AddonInstaller $installer): RedirectResponse
    {
        if (! Schema::hasTable('modules')) {
            abort(503, 'Modules table is missing.');
        }

        $data = $request->validate([
            'zip' => ['required', 'file', 'mimes:zip', 'max:51200'], // 50MB
            'force' => ['nullable', 'boolean'],
        ]);

        $manifest = $installer->installFromZip($request->file('zip'), (bool) ($data['force'] ?? false));

        $registry = app(ModuleRegistry::class);
        $registry->syncDatabase();
        $registry->registerPermissions($registry->discover());

        ActivityLogger::log('module.installed', null, "Module installed: {$manifest->slug}");

        return back()->with('status', 'Module installed. Run migrations if the add-on includes database changes.');
    }

    public function uninstall(Request $request, Module $module): RedirectResponse
    {
        if (! Schema::hasTable('modules')) {
            abort(503, 'Modules table is missing.');
        }

        $registry = app(ModuleRegistry::class);
        $manifest = collect($registry->discover())->firstWhere('slug', $module->slug);
        abort_unless($manifest, 404);

        $moduleDir = base_path('modules'.DIRECTORY_SEPARATOR.$manifest->directory);
        $marker = $moduleDir.DIRECTORY_SEPARATOR.'.addon-installed';
        abort_unless(File::exists($marker), 403, 'Only zip-installed add-ons can be uninstalled from the UI.');

        $module->forceFill(['enabled' => false])->save();
        File::deleteDirectory($moduleDir);
        $module->delete();

        $registry->syncDatabase();

        ActivityLogger::log('module.uninstalled', null, "Module uninstalled: {$manifest->slug}");

        return back()->with('status', 'Module uninstalled.');
    }

    public function regenerateAddonKey(Request $request): RedirectResponse
    {
        $user = $request->user();
        $org = $user?->currentOrganization;
        abort_unless($org instanceof Organization, 403, 'Organization is required.');

        $plain = 'addon_'.Str::random(48);
        $org->forceFill(['addon_api_key_hash' => hash('sha256', $plain)])->save();

        ActivityLogger::log('addon.key_regenerated', $org, 'Add-on API key regenerated');

        return back()
            ->with('addon_api_key', $plain)
            ->with('status', 'Add-on API key generated. Copy it now; it will not be shown again.');
    }
}
