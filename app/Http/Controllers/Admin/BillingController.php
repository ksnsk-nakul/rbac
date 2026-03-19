<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Razorpay\RazorpayClient;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(Request $request): Response
    {
        $org = $request->user()?->currentOrganization;
        abort_unless($org, 403);

        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $subscription = Subscription::query()
            ->where('organization_id', $org->id)
            ->orderByDesc('id')
            ->first();

        return Inertia::render('admin/Billing', [
            'organization' => $org->only(['id', 'name', 'slug']),
            'plans' => $plans,
            'subscription' => $subscription,
            'razorpayKey' => (string) \App\Models\Setting::getValueSafe('payment.razorpay_key', env('RAZORPAY_KEY_ID', '')),
        ]);
    }

    public function checkout(Request $request, Plan $plan, RazorpayClient $razorpay): RedirectResponse
    {
        $request->validate([
            'interval' => ['required', 'in:monthly,yearly'],
        ]);

        $org = $request->user()?->currentOrganization;
        abort_unless($org, 403);

        $interval = (string) $request->input('interval');
        $providerPlanId = $interval === 'yearly' ? $plan->razorpay_plan_id_yearly : $plan->razorpay_plan_id_monthly;
        abort_if(! $providerPlanId, 422, 'This plan is not configured for Razorpay.');

        $payload = [
            'plan_id' => $providerPlanId,
            'total_count' => 120,
            'quantity' => 1,
            'customer_notify' => 1,
            'notes' => [
                'organization_id' => (string) $org->id,
                'plan_slug' => $plan->slug,
                'interval' => $interval,
            ],
        ];

        $subscriptionData = $razorpay->createSubscription($payload);

        $subscription = Subscription::create([
            'organization_id' => $org->id,
            'plan_id' => $plan->id,
            'provider' => 'razorpay',
            'provider_subscription_id' => (string) ($subscriptionData['id'] ?? ''),
            'status' => (string) ($subscriptionData['status'] ?? 'created'),
            'interval' => $interval,
            'quantity' => 1,
            'provider_payload' => $subscriptionData,
        ]);

        ActivityLogger::log($request, 'billing.checkout_created', $subscription, "Checkout created for plan {$plan->slug}");

        $key = (string) \App\Models\Setting::getValueSafe('payment.razorpay_key', env('RAZORPAY_KEY_ID', ''));

        return back()
            ->with('status', 'Subscription created. Complete payment in Razorpay checkout.')
            ->with('checkout', [
                'provider' => 'razorpay',
                'key' => $key,
                'subscription_id' => $subscription->provider_subscription_id,
                'name' => config('app.name', 'App'),
                'description' => "Subscription: {$plan->name} ({$interval})",
                'prefill' => [
                    'email' => (string) ($request->user()?->email ?? ''),
                    'name' => (string) ($request->user()?->name ?? ''),
                ],
            ]);
    }

    public function cancel(Request $request, Subscription $subscription, RazorpayClient $razorpay): RedirectResponse
    {
        $org = $request->user()?->currentOrganization;
        abort_unless($org, 403);
        abort_unless((int) $subscription->organization_id === (int) $org->id, 403);

        $razorpay->cancelSubscription($subscription->provider_subscription_id);

        $subscription->forceFill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ])->save();

        ActivityLogger::log($request, 'billing.subscription_cancelled', $subscription, 'Subscription cancelled');

        return back()->with('status', 'Subscription cancelled.');
    }
}
