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

class IntegrationsController extends Controller
{
    public function index(): Response
    {
        if (! Schema::hasTable('settings')) {
            return Inertia::render('admin/Integrations', [
                'providers' => $this->providers(),
                'values' => [],
            ]);
        }

        return Inertia::render('admin/Integrations', [
            'providers' => $this->providers(),
            'values' => $this->currentValues(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('settings')) {
            abort(503, 'Settings table is missing.');
        }

        $fields = $this->fields();
        $rules = [];

        foreach ($fields as $field) {
            $rules[$field['key']] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);

        foreach ($fields as $field) {
            if (! array_key_exists($field['key'], $validated)) {
                continue;
            }

            Setting::setValue($field['key'], $validated[$field['key']], $field['group']);
        }

        ActivityLogger::log('integrations.updated', null, 'Integration settings updated');

        return back()->with('status', 'Integrations updated.');
    }

    private function providers(): array
    {
        return [
            // Keep this starter kit focused: one payment provider (Razorpay),
            // and generic Mailer/SMS fields that map to Laravel config/env.
            'sms' => ['twilio', 'msg91', 'other'],
            'payment' => ['razorpay'],
            'mailer' => ['smtp'],
        ];
    }

    private function fields(): array
    {
        return [
            ['group' => 'sms', 'key' => 'sms.provider'],
            ['group' => 'sms', 'key' => 'sms.api_key'],
            ['group' => 'sms', 'key' => 'sms.sender_id'],

            ['group' => 'mailer', 'key' => 'mailer.driver'],
            ['group' => 'mailer', 'key' => 'mailer.host'],
            ['group' => 'mailer', 'key' => 'mailer.port'],
            ['group' => 'mailer', 'key' => 'mailer.username'],
            ['group' => 'mailer', 'key' => 'mailer.password'],
            ['group' => 'mailer', 'key' => 'mailer.encryption'],
            ['group' => 'mailer', 'key' => 'mailer.from_address'],
            ['group' => 'mailer', 'key' => 'mailer.from_name'],

            ['group' => 'payment', 'key' => 'payment.provider'],
            ['group' => 'payment', 'key' => 'payment.razorpay_key'],
            ['group' => 'payment', 'key' => 'payment.razorpay_secret'],
            ['group' => 'payment', 'key' => 'payment.razorpay_webhook_secret'],
        ];
    }

    private function currentValues(): array
    {
        $values = [];
        foreach ($this->fields() as $field) {
            $values[$field['key']] = Setting::getValue($field['key']);
        }

        return $values;
    }
}
