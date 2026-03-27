<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        if (! Schema::hasTable('settings')) {
            return Inertia::render('admin/Settings', [
                'values' => [
                    'system.app_name' => config('app.name'),
                    'system.support_email' => null,
                    'system.theme_default' => 'system',
                    'system.theme_color' => '#f97316',
                    'system.app_logo' => null,
                    'system.app_favicon' => null,
                ],
            ]);
        }

        return Inertia::render('admin/Settings', [
            'values' => [
                'system.app_name' => Setting::getValue('system.app_name', config('app.name')),
                'system.support_email' => Setting::getValue('system.support_email', null),
                'system.theme_default' => Setting::getValue('system.theme_default', 'system'),
                'system.theme_color' => Setting::getValue('system.theme_color', '#f97316'),
                'system.app_logo' => Setting::getValue('system.app_logo', null),
                'system.app_favicon' => Setting::getValue('system.app_favicon', null),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('settings')) {
            abort(503, 'Settings table is missing.');
        }

        $validated = $request->validate([
            'system.app_name' => ['nullable', 'string', 'max:255'],
            'system.support_email' => ['nullable', 'string', 'email', 'max:255'],
            'system.theme_default' => ['nullable', 'in:light,dark,system'],
            'system.theme_color' => ['nullable', 'string', 'max:32'],
            'system.app_logo' => ['nullable', 'image', 'max:2048'],
            'system.app_favicon' => ['nullable', 'mimes:png,svg,ico', 'max:1024'],
        ]);

        Setting::setValue('system.app_name', $validated['system.app_name'] ?? null, 'system');
        Setting::setValue('system.support_email', $validated['system.support_email'] ?? null, 'system');
        Setting::setValue('system.theme_default', $validated['system.theme_default'] ?? 'system', 'system');
        Setting::setValue('system.theme_color', $validated['system.theme_color'] ?? '#f97316', 'system');

        if ($request->hasFile('system.app_logo')) {
            $path = $request->file('system.app_logo')->store('settings', 'public');
            Setting::setValue('system.app_logo', $path, 'system');
        }

        if ($request->hasFile('system.app_favicon')) {
            $path = $request->file('system.app_favicon')->store('settings', 'public');
            Setting::setValue('system.app_favicon', $path, 'system');
        }

        ActivityLogger::log('settings.updated', null, 'System settings updated');

        return back()->with('status', 'Settings updated.');
    }
}
