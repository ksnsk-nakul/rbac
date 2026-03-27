<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    providers: Record<string, string[]>;
    values: Record<string, string | null>;
}>();

const form = useForm({
    ...props.values,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/dashboard' },
    { title: 'Integrations', href: '/admin/integrations' },
];

const submit = () => {
    form.patch('/admin/integrations');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Integrations" />

        <div class="space-y-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Integrations</h1>
                <p class="text-sm text-muted-foreground">Manage external service providers and credentials.</p>
            </div>

            <form class="space-y-6" @submit.prevent="submit">
                <section class="rounded-lg border p-4 space-y-3">
                    <h2 class="text-lg font-medium">SMS Providers</h2>
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="space-y-1 text-sm">
                            <span>Provider</span>
                            <select v-model="form['sms.provider']" class="w-full rounded-md border px-3 py-2">
                                <option value="">Select provider</option>
                                <option v-for="provider in props.providers.sms" :key="provider" :value="provider">
                                    {{ provider }}
                                </option>
                            </select>
                        </label>
                        <label class="space-y-1 text-sm">
                            <span>API key</span>
                            <input v-model="form['sms.api_key']" type="password" class="w-full rounded-md border px-3 py-2" />
                        </label>
                        <label class="space-y-1 text-sm">
                            <span>Sender ID</span>
                            <input v-model="form['sms.sender_id']" class="w-full rounded-md border px-3 py-2" />
                        </label>
                    </div>
                </section>

                <section class="rounded-lg border p-4 space-y-3">
                    <h2 class="text-lg font-medium">Payment Providers</h2>
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="space-y-1 text-sm">
                            <span>Provider</span>
                            <select v-model="form['payment.provider']" class="w-full rounded-md border px-3 py-2">
                                <option value="">Select provider</option>
                                <option v-for="provider in props.providers.payment" :key="provider" :value="provider">
                                    {{ provider }}
                                </option>
                            </select>
                        </label>
                        <label class="space-y-1 text-sm">
                            <span>Razorpay Key</span>
                            <input v-model="form['payment.razorpay_key']" class="w-full rounded-md border px-3 py-2" />
                        </label>
                        <label class="space-y-1 text-sm">
                            <span>Razorpay Secret</span>
                            <input v-model="form['payment.razorpay_secret']" type="password" class="w-full rounded-md border px-3 py-2" />
                        </label>
                        <label class="space-y-1 text-sm">
                            <span>Razorpay Webhook Secret</span>
                            <input v-model="form['payment.razorpay_webhook_secret']" type="password" class="w-full rounded-md border px-3 py-2" />
                        </label>
                    </div>
                </section>

                <section class="rounded-lg border p-4 space-y-3">
                    <h2 class="text-lg font-medium">Mailer Providers</h2>
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="space-y-1 text-sm">
                            <span>Driver</span>
                            <input v-model="form['mailer.driver']" class="w-full rounded-md border px-3 py-2" placeholder="smtp" />
                        </label>
                        <label class="space-y-1 text-sm">
                            <span>Host</span>
                            <input v-model="form['mailer.host']" class="w-full rounded-md border px-3 py-2" placeholder="smtp.example.com" />
                        </label>
                        <label class="space-y-1 text-sm">
                            <span>Port</span>
                            <input v-model="form['mailer.port']" class="w-full rounded-md border px-3 py-2" placeholder="587" />
                        </label>
                        <label class="space-y-1 text-sm">
                            <span>Username</span>
                            <input v-model="form['mailer.username']" class="w-full rounded-md border px-3 py-2" />
                        </label>
                        <label class="space-y-1 text-sm">
                            <span>Password</span>
                            <input v-model="form['mailer.password']" type="password" class="w-full rounded-md border px-3 py-2" />
                        </label>
                        <label class="space-y-1 text-sm">
                            <span>Encryption</span>
                            <input v-model="form['mailer.encryption']" class="w-full rounded-md border px-3 py-2" placeholder="tls" />
                        </label>
                        <label class="space-y-1 text-sm">
                            <span>From address</span>
                            <input v-model="form['mailer.from_address']" class="w-full rounded-md border px-3 py-2" placeholder="no-reply@example.com" />
                        </label>
                        <label class="space-y-1 text-sm">
                            <span>From name</span>
                            <input v-model="form['mailer.from_name']" class="w-full rounded-md border px-3 py-2" />
                        </label>
                    </div>
                </section>

                <div class="flex items-center gap-2">
                    <button class="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground" type="submit">
                        Save integrations
                    </button>
                    <span v-if="form.recentlySuccessful" class="text-xs text-muted-foreground">Saved.</span>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
