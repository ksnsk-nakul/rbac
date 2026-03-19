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
                'mailer.driver' => 'smtp',
                'mailer.host' => null,
                'mailer.port' => null,
                'mailer.username' => null,
                'mailer.password' => null,
                'mailer.encryption' => null,
                'mailer.from_address' => null,
                'mailer.from_name' => null,
                'sms.provider' => null,
                'sms.api_key' => null,
                'sms.sender_id' => null,
                'payment.provider' => null,
                'payment.public_key' => null,
                'payment.secret_key' => null,
                'payment.webhook_secret' => null,
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
                'mailer.driver' => Setting::getValue('mailer.driver', 'smtp'),
                'mailer.host' => Setting::getValue('mailer.host', null),
                'mailer.port' => Setting::getValue('mailer.port', null),
                'mailer.username' => Setting::getValue('mailer.username', null),
                'mailer.password' => Setting::getValue('mailer.password', null),
                'mailer.encryption' => Setting::getValue('mailer.encryption', null),
                'mailer.from_address' => Setting::getValue('mailer.from_address', null),
                'mailer.from_name' => Setting::getValue('mailer.from_name', null),
                'sms.provider' => Setting::getValue('sms.provider', null),
                'sms.api_key' => Setting::getValue('sms.api_key', null),
                'sms.sender_id' => Setting::getValue('sms.sender_id', null),
                'payment.provider' => Setting::getValue('payment.provider', null),
                'payment.public_key' => Setting::getValue('payment.public_key', null),
                'payment.secret_key' => Setting::getValue('payment.secret_key', null),
                'payment.webhook_secret' => Setting::getValue('payment.webhook_secret', null),
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
            'mailer.driver' => ['nullable', 'string', 'max:50'],
            'mailer.host' => ['nullable', 'string', 'max:255'],
            'mailer.port' => ['nullable', 'string', 'max:10'],
            'mailer.username' => ['nullable', 'string', 'max:255'],
            'mailer.password' => ['nullable', 'string', 'max:255'],
            'mailer.encryption' => ['nullable', 'string', 'max:50'],
            'mailer.from_address' => ['nullable', 'string', 'email', 'max:255'],
            'mailer.from_name' => ['nullable', 'string', 'max:255'],
            'sms.provider' => ['nullable', 'string', 'max:50'],
            'sms.api_key' => ['nullable', 'string', 'max:255'],
            'sms.sender_id' => ['nullable', 'string', 'max:255'],
            'payment.provider' => ['nullable', 'string', 'max:50'],
            'payment.public_key' => ['nullable', 'string', 'max:255'],
            'payment.secret_key' => ['nullable', 'string', 'max:255'],
            'payment.webhook_secret' => ['nullable', 'string', 'max:255'],
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

        Setting::setValue('mailer.driver', $validated['mailer.driver'] ?? null, 'mailer');
        Setting::setValue('mailer.host', $validated['mailer.host'] ?? null, 'mailer');
        Setting::setValue('mailer.port', $validated['mailer.port'] ?? null, 'mailer');
        Setting::setValue('mailer.username', $validated['mailer.username'] ?? null, 'mailer');
        Setting::setValue('mailer.password', $validated['mailer.password'] ?? null, 'mailer');
        Setting::setValue('mailer.encryption', $validated['mailer.encryption'] ?? null, 'mailer');
        Setting::setValue('mailer.from_address', $validated['mailer.from_address'] ?? null, 'mailer');
        Setting::setValue('mailer.from_name', $validated['mailer.from_name'] ?? null, 'mailer');

        Setting::setValue('sms.provider', $validated['sms.provider'] ?? null, 'sms');
        Setting::setValue('sms.api_key', $validated['sms.api_key'] ?? null, 'sms');
        Setting::setValue('sms.sender_id', $validated['sms.sender_id'] ?? null, 'sms');

        Setting::setValue('payment.provider', $validated['payment.provider'] ?? null, 'payment');
        Setting::setValue('payment.public_key', $validated['payment.public_key'] ?? null, 'payment');
        Setting::setValue('payment.secret_key', $validated['payment.secret_key'] ?? null, 'payment');
        Setting::setValue('payment.webhook_secret', $validated['payment.webhook_secret'] ?? null, 'payment');

        ActivityLogger::log('settings.updated', null, 'System settings updated');

        return back()->with('status', 'Settings updated.');
    }
}
