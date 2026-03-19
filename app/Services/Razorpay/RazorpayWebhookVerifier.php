<?php

namespace App\Services\Razorpay;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RazorpayWebhookVerifier
{
    public function verify(Request $request): bool
    {
        $signature = (string) $request->header('X-Razorpay-Signature', '');
        if ($signature === '') {
            return false;
        }

        $secret = (string) env('RAZORPAY_WEBHOOK_SECRET', '');
        if (Schema::hasTable('settings')) {
            $secret = (string) Setting::getValueSafe('payment.razorpay_webhook_secret', $secret);
        }

        if ($secret === '') {
            return false;
        }

        $expected = hash_hmac('sha256', (string) $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }
}
