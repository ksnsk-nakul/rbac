<?php

namespace App\Http\Middleware;

use App\Modules\ModuleRegistry;
use App\Models\Organization;
use App\Services\DeviceFingerprint;
use App\Services\PlanGate;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $demoCredentials = null;
        $user = $request->user()?->loadMissing('role.permissions');
        $permissions = [];
        $moduleNav = [];
        $currentOrganization = null;
        $appSettings = [
            'app_name' => config('app.name'),
            'theme_default' => 'system',
            'theme_color' => '#f97316',
            'app_logo' => null,
            'app_favicon' => null,
        ];
        $showTrustDeviceModal = false;

        if ($user) {
            $permissions = $user->isAdmin()
                ? ['*']
                : ($user->role?->permissions?->pluck('slug')->all() ?? []);
        }

        if ($user && \Illuminate\Support\Facades\Schema::hasTable('organizations')) {
            $currentOrganization = $user->currentOrganization;
            if (! $currentOrganization) {
                $orgId = $user->organizations()
                    ->wherePivot('status', 'active')
                    ->orderBy('organizations.id')
                    ->limit(1)
                    ->pluck('organizations.id')
                    ->first();

                if ($orgId) {
                    $user->forceFill(['current_organization_id' => $orgId])->save();
                    $currentOrganization = Organization::find($orgId);
                }
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $appSettings = [
                'app_name' => \App\Models\Setting::getValue('system.app_name', config('app.name')),
                'theme_default' => \App\Models\Setting::getValue('system.theme_default', 'system'),
                'theme_color' => \App\Models\Setting::getValue('system.theme_color', '#f97316'),
                'app_logo' => \App\Models\Setting::getValue('system.app_logo', null),
                'app_favicon' => \App\Models\Setting::getValue('system.app_favicon', null),
            ];
        }

        if ($user) {
            $role = $user->currentRole();
            if ($role && $role->mfa_required && PlanGate::allows($user, 'mfa_enforcement')) {
                if ($user->two_factor_confirmed_at && $user->two_factor_secret) {
                    $fingerprint = DeviceFingerprint::fromRequest($request, $user->id);
                    $trusted = $user->trustedDevices()->where('device_fingerprint', $fingerprint)->exists();
                    $showTrustDeviceModal = ! $trusted;
                }
            }
        }

        if (
            app()->environment(['local', 'development', 'dev'])
            && filter_var(env('SEED_DEMO_ADMIN', false), FILTER_VALIDATE_BOOL)
        ) {
            $demoCredentials = [
                'admin' => [
                    'email' => (string) env('DEMO_ADMIN_EMAIL', 'admin@admin.com'),
                    'password' => (string) env('DEMO_ADMIN_PASSWORD', '1234567890'),
                ],
            ];
        }

        if (app()->bound(ModuleRegistry::class)) {
            $registry = app(ModuleRegistry::class);
            $moduleNav = $registry->navigation($registry->enabled());
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'permissions' => $permissions,
                'showTrustDeviceModal' => $showTrustDeviceModal,
            ],
            'currentOrganization' => $currentOrganization,
            'moduleNav' => $moduleNav,
            'appSettings' => $appSettings,
            'demoCredentials' => $demoCredentials,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'checkout' => fn () => $request->session()->get('checkout'),
                'addonApiKey' => fn () => $request->session()->get('addon_api_key'),
            ],
        ];
    }
}
