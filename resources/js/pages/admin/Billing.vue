<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Plan = {
    id: number;
    name: string;
    slug: string;
    currency: string;
    price_monthly: number | null;
    price_yearly: number | null;
    razorpay_plan_id_monthly: string | null;
    razorpay_plan_id_yearly: string | null;
};

type Subscription = {
    id: number;
    status: string;
    interval: string | null;
    provider_subscription_id: string;
    current_period_ends_at: string | null;
    plan_id: number | null;
};

const props = defineProps<{
    organization: { id: number; name: string; slug: string };
    plans: Plan[];
    subscription: Subscription | null;
    razorpayKey: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Billing', href: '/admin/billing' },
];

const checkoutForm = useForm<{ interval: 'monthly' | 'yearly' }>({ interval: 'monthly' });

const formatMoney = (amountMinor: number | null, currency: string) => {
    if (amountMinor == null) return '—';
    const major = amountMinor / 100;
    return new Intl.NumberFormat(undefined, { style: 'currency', currency }).format(major);
};

const checkout = (planId: number) => {
    checkoutForm.post(`/admin/billing/checkout/${planId}`);
};

const cancelForm = useForm({});
const cancel = (subscriptionId: number) => {
    cancelForm.post(`/admin/billing/subscriptions/${subscriptionId}/cancel`);
};

const page = usePage();

type RazorpayCheckout = {
    provider: 'razorpay';
    key: string;
    subscription_id: string;
    name: string;
    description: string;
    prefill: { email?: string; name?: string };
};

const loadRazorpay = async (): Promise<void> => {
    if ((window as any).Razorpay) return;
    await new Promise<void>((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Failed to load Razorpay'));
        document.head.appendChild(script);
    });
};

onMounted(async () => {
    const checkout = (page.props.flash as any)?.checkout as RazorpayCheckout | undefined;
    if (!checkout || checkout.provider !== 'razorpay') return;
    if (!checkout.key || !checkout.subscription_id) return;

    try {
        await loadRazorpay();
        const options = {
            key: checkout.key,
            subscription_id: checkout.subscription_id,
            name: checkout.name,
            description: checkout.description,
            prefill: checkout.prefill,
            theme: { color: 'var(--color-primary)' },
        };

        const rz = new (window as any).Razorpay(options);
        rz.open();
    } catch {
        // If checkout fails to load, user can retry by clicking "Choose plan" again.
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Billing" />

        <div class="space-y-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Billing</h1>
                <p class="text-sm text-muted-foreground">
                    Manage plans and subscriptions for {{ props.organization.name }}.
                </p>
            </div>

            <section class="rounded-xl border bg-card p-5">
                <h2 class="text-lg font-semibold">Current subscription</h2>
                <div v-if="props.subscription" class="mt-3 grid gap-2 text-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="text-muted-foreground">Status</div>
                        <div class="font-medium">{{ props.subscription.status }}</div>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="text-muted-foreground">Interval</div>
                        <div class="font-medium">{{ props.subscription.interval ?? '—' }}</div>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="text-muted-foreground">Renews</div>
                        <div class="font-medium">{{ props.subscription.current_period_ends_at ?? '—' }}</div>
                    </div>

                    <div class="mt-4">
                        <button
                            class="rounded-md border px-4 py-2 text-sm"
                            type="button"
                            :disabled="cancelForm.processing"
                            @click="cancel(props.subscription.id)"
                        >
                            Cancel subscription
                        </button>
                    </div>
                </div>
                <div v-else class="mt-3 text-sm text-muted-foreground">
                    No subscription yet.
                </div>
            </section>

            <section class="rounded-xl border bg-card p-5 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold">Plans</h2>
                        <p class="text-sm text-muted-foreground">
                            Configure Razorpay plan IDs in Admin Integrations and seed plan IDs in the database.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-muted-foreground">Interval</label>
                        <select v-model="checkoutForm.interval" class="rounded-md border px-3 py-2 text-sm">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div v-for="plan in props.plans" :key="plan.id" class="rounded-xl border bg-background p-4">
                        <div class="text-base font-semibold">{{ plan.name }}</div>
                        <div class="mt-1 text-xs text-muted-foreground">{{ plan.slug }}</div>
                        <div class="mt-4 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground">Monthly</span>
                                <span class="font-medium">{{ formatMoney(plan.price_monthly, plan.currency) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground">Yearly</span>
                                <span class="font-medium">{{ formatMoney(plan.price_yearly, plan.currency) }}</span>
                            </div>
                        </div>

                        <button
                            class="mt-4 w-full rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground disabled:opacity-50"
                            type="button"
                            :disabled="checkoutForm.processing || !props.razorpayKey"
                            @click="checkout(plan.id)"
                        >
                            Choose plan
                        </button>
                        <div v-if="!props.razorpayKey" class="mt-2 text-xs text-muted-foreground">
                            Configure Razorpay key in Integrations first.
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
