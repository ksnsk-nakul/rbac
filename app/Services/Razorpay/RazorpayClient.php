<?php

namespace App\Services\Razorpay;

use App\Models\Setting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class RazorpayClient
{
    private const BASE_URL = 'https://api.razorpay.com/v1';

    public function request(): PendingRequest
    {
        [$key, $secret] = $this->credentials();

        return Http::baseUrl(self::BASE_URL)
            ->acceptJson()
            ->asJson()
            ->withBasicAuth($key, $secret);
    }

    public function createSubscription(array $payload): array
    {
        $response = $this->request()->post('/subscriptions', $payload);
        $response->throw();

        return (array) $response->json();
    }

    public function fetchSubscription(string $subscriptionId): array
    {
        $response = $this->request()->get("/subscriptions/{$subscriptionId}");
        $response->throw();

        return (array) $response->json();
    }

    public function cancelSubscription(string $subscriptionId): array
    {
        $response = $this->request()->post("/subscriptions/{$subscriptionId}/cancel");
        $response->throw();

        return (array) $response->json();
    }

    private function credentials(): array
    {
        $key = (string) env('RAZORPAY_KEY_ID', '');
        $secret = (string) env('RAZORPAY_KEY_SECRET', '');

        if (Schema::hasTable('settings')) {
            $key = (string) Setting::getValueSafe('payment.razorpay_key', $key);
            $secret = (string) Setting::getValueSafe('payment.razorpay_secret', $secret);
        }

        if ($key === '' || $secret === '') {
            throw new \RuntimeException('Razorpay credentials are not configured.');
        }

        return [$key, $secret];
    }
}
