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
            'sms' => ['Twilio', 'MSG91', 'Coding Mantra', 'AWS SNS'],
            'payment' => ['Stripe', 'Razorpay', 'PayPal'],
            'mailer' => ['SMTP', 'Mailgun', 'Amazon SES'],
        ];
    }

    private function fields(): array
    {
        return [
            ['group' => 'sms', 'key' => 'sms.provider'],
            ['group' => 'sms', 'key' => 'sms.twilio_sid'],
            ['group' => 'sms', 'key' => 'sms.twilio_token'],
            ['group' => 'sms', 'key' => 'sms.msg91_key'],
            ['group' => 'sms', 'key' => 'sms.coding_mantra_key'],
            ['group' => 'sms', 'key' => 'sms.aws_sns_key'],
            ['group' => 'sms', 'key' => 'sms.aws_sns_secret'],
            ['group' => 'payment', 'key' => 'payment.provider'],
            ['group' => 'payment', 'key' => 'payment.stripe_key'],
            ['group' => 'payment', 'key' => 'payment.stripe_secret'],
            ['group' => 'payment', 'key' => 'payment.razorpay_key'],
            ['group' => 'payment', 'key' => 'payment.razorpay_secret'],
            ['group' => 'payment', 'key' => 'payment.razorpay_webhook_secret'],
            ['group' => 'payment', 'key' => 'payment.paypal_client_id'],
            ['group' => 'payment', 'key' => 'payment.paypal_secret'],
            ['group' => 'mailer', 'key' => 'mailer.provider'],
            ['group' => 'mailer', 'key' => 'mailer.smtp_host'],
            ['group' => 'mailer', 'key' => 'mailer.smtp_port'],
            ['group' => 'mailer', 'key' => 'mailer.smtp_user'],
            ['group' => 'mailer', 'key' => 'mailer.smtp_password'],
            ['group' => 'mailer', 'key' => 'mailer.mailgun_key'],
            ['group' => 'mailer', 'key' => 'mailer.ses_key'],
            ['group' => 'mailer', 'key' => 'mailer.ses_secret'],
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
