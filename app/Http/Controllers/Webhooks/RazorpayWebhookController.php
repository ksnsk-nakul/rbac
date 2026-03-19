<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\PaymentEvent;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Razorpay\RazorpayWebhookVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RazorpayWebhookController extends Controller
{
    public function __invoke(Request $request, RazorpayWebhookVerifier $verifier): JsonResponse
    {
        if (! $verifier->verify($request)) {
            return response()->json(['ok' => false], 401);
        }

        $payload = (array) $request->json()->all();
        $eventId = (string) ($payload['event_id'] ?? $payload['id'] ?? '');
        $type = (string) ($payload['event'] ?? $payload['type'] ?? '');

        if ($eventId === '' || $type === '') {
            return response()->json(['ok' => false, 'error' => 'invalid_payload'], 422);
        }

        $event = PaymentEvent::firstOrCreate(
            ['provider_event_id' => $eventId],
            ['provider' => 'razorpay', 'type' => $type, 'payload' => $payload]
        );

        if ($event->processed_at) {
            return response()->json(['ok' => true]);
        }

        $this->handleEvent($payload, $type);

        $event->forceFill(['processed_at' => now()])->save();

        return response()->json(['ok' => true]);
    }

    private function handleEvent(array $payload, string $type): void
    {
        $subscriptionId = (string) data_get($payload, 'payload.subscription.entity.id', '');
        if ($subscriptionId === '') {
            return;
        }

        $subscription = Subscription::where('provider_subscription_id', $subscriptionId)->first();
        if (! $subscription) {
            return;
        }

        $status = (string) data_get($payload, 'payload.subscription.entity.status', $subscription->status);
        $currentEnd = data_get($payload, 'payload.subscription.entity.current_end');
        $trialEnd = data_get($payload, 'payload.subscription.entity.trial_end');

        $subscription->forceFill([
            'status' => $status,
            'current_period_ends_at' => is_numeric($currentEnd) ? now()->setTimestamp((int) $currentEnd) : $subscription->current_period_ends_at,
            'trial_ends_at' => is_numeric($trialEnd) ? now()->setTimestamp((int) $trialEnd) : $subscription->trial_ends_at,
            'provider_payload' => (array) data_get($payload, 'payload.subscription.entity', $subscription->provider_payload),
        ])->save();

        // Sync organization plan from subscription plan when it becomes active.
        if (in_array($status, ['active', 'authenticated'], true)) {
            $org = Organization::find($subscription->organization_id);
            $plan = $subscription->plan_id ? Plan::find($subscription->plan_id) : null;
            if ($org && $plan && $org->plan_id !== $plan->id) {
                $org->forceFill(['plan_id' => $plan->id, 'billing_provider' => 'razorpay'])->save();
            }
        }
    }
}
