<?php

namespace App\Services;

use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Http;

class SecurityWebhookService
{
    public const EVENTS = [
        'user_login',
        'user_logout',
        'role_changed',
        'permission_changed',
        'token_created',
        'token_revoked',
    ];

    public static function dispatch(string $event, array $payload): void
    {
        $endpoints = WebhookEndpoint::query()
            ->where('active', true)
            ->get();

        foreach ($endpoints as $endpoint) {
            if (!in_array($event, $endpoint->events ?? [], true)) {
                continue;
            }

            $body = [
                'event' => $event,
                'payload' => $payload,
            ];

            $signature = null;
            if (!empty($endpoint->secret)) {
                $signature = hash_hmac('sha256', json_encode($body), $endpoint->secret);
            }

            Http::timeout(5)
                ->withHeaders(array_filter([
                    'X-Webhook-Signature' => $signature,
                ]))
                ->post($endpoint->url, $body);
        }
    }
}
