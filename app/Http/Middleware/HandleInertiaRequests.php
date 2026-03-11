<?php

namespace App\Http\Middleware;

use App\Modules\ModuleRegistry;
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

        if ($user) {
            $permissions = $user->isAdmin()
                ? ['*']
                : ($user->role?->permissions?->pluck('slug')->all() ?? []);
        }

        if (app()->environment(['local', 'development', 'dev'])) {
            $demoCredentials = [
                'admin' => [
                    'email' => 'admin@admin.com',
                    'password' => '1234567890',
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
            ],
            'moduleNav' => $moduleNav,
            'demoCredentials' => $demoCredentials,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
        ];
    }
}
