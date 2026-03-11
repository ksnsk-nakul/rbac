<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
                ],
            ]);
        }

        return Inertia::render('admin/Settings', [
            'values' => [
                'system.app_name' => Setting::getValue('system.app_name', config('app.name')),
                'system.support_email' => Setting::getValue('system.support_email', null),
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
        ]);

        Setting::setValue('system.app_name', $validated['system.app_name'] ?? null, 'system');
        Setting::setValue('system.support_email', $validated['system.support_email'] ?? null, 'system');

        ActivityLogger::log('settings.updated', null, 'System settings updated');

        return back()->with('status', 'Settings updated.');
    }
}
