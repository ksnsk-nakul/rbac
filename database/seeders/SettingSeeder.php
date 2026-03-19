<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'system.app_name' => 'RBAC Starter',
            'system.support_email' => 'support@example.com',
            'system.theme_default' => 'system',
            'system.theme_color' => '#f97316',
            'system.app_logo' => null,
            'system.app_favicon' => null,
            'mailer.driver' => 'smtp',
            'mailer.host' => 'smtp.example.com',
            'mailer.port' => '587',
            'mailer.username' => null,
            'mailer.password' => null,
            'mailer.encryption' => 'tls',
            'mailer.from_address' => 'no-reply@example.com',
            'mailer.from_name' => 'RBAC Starter',
            'sms.provider' => 'twilio',
            'sms.api_key' => null,
            'sms.sender_id' => null,
            'payment.provider' => 'razorpay',
            'payment.razorpay_key' => null,
            'payment.razorpay_secret' => null,
            'payment.razorpay_webhook_secret' => null,
            'payment.stripe_key' => null,
            'payment.stripe_secret' => null,
            'license.key' => null,
        ];

        foreach ($defaults as $key => $value) {
            Setting::setValue($key, Setting::getValueSafe($key, $value), explode('.', $key, 2)[0]);
        }
    }
}
