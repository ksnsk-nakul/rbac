<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    values: Record<string, string | null>;
}>();

const form = useForm({
    ...props.values,
    'system.app_logo': null as File | null,
    'system.app_favicon': null as File | null,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/dashboard' },
    { title: 'Settings', href: '/admin/settings' },
];

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'patch',
    }));
    form.post('/admin/settings', { forceFormData: true });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Settings" />

        <div class="space-y-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Settings</h1>
                <p class="text-sm text-muted-foreground">System-level configuration.</p>
            </div>

            <form class="space-y-4 max-w-xl" @submit.prevent="submit">
                <label class="space-y-1 text-sm">
                    <span>Application name</span>
                    <input v-model="form['system.app_name']" class="w-full rounded-md border px-3 py-2" />
                </label>
                <label class="space-y-1 text-sm">
                    <span>Default theme</span>
                    <select v-model="form['system.theme_default']" class="w-full rounded-md border px-3 py-2">
                        <option value="system">System</option>
                        <option value="light">Light</option>
                        <option value="dark">Dark</option>
                    </select>
                </label>
                <label class="space-y-1 text-sm">
                    <span>Theme color (active)</span>
                    <input v-model="form['system.theme_color']" class="w-full rounded-md border px-3 py-2" placeholder="#f97316" />
                    <p class="text-xs text-muted-foreground">Use a hex or CSS color value.</p>
                </label>
                <label class="space-y-1 text-sm">
                    <span>Support email</span>
                    <input v-model="form['system.support_email']" class="w-full rounded-md border px-3 py-2" />
                </label>
                <label class="space-y-1 text-sm">
                    <span>App logo</span>
                    <input
                        type="file"
                        accept="image/*"
                        class="w-full rounded-md border px-3 py-2"
                        @change="form['system.app_logo'] = $event.target.files?.[0] || null"
                    />
                    <p v-if="props.values['system.app_logo']" class="text-xs text-muted-foreground">
                        Current: <span class="truncate">{{ props.values['system.app_logo'] }}</span>
                    </p>
                    <p v-if="form['system.app_logo']" class="text-xs text-muted-foreground">
                        Selected: {{ form['system.app_logo']?.name }}
                    </p>
                </label>
                <label class="space-y-1 text-sm">
                    <span>Favicon (.png, .svg, .ico)</span>
                    <input
                        type="file"
                        accept=".png,.svg,.ico"
                        class="w-full rounded-md border px-3 py-2"
                        @change="form['system.app_favicon'] = $event.target.files?.[0] || null"
                    />
                    <p v-if="props.values['system.app_favicon']" class="text-xs text-muted-foreground">
                        Current: <span class="truncate">{{ props.values['system.app_favicon'] }}</span>
                    </p>
                    <p v-if="form['system.app_favicon']" class="text-xs text-muted-foreground">
                        Selected: {{ form['system.app_favicon']?.name }}
                    </p>
                </label>

                <div class="flex items-center gap-2">
                    <button class="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground" type="submit">
                        Save settings
                    </button>
                    <span v-if="form.recentlySuccessful" class="text-xs text-muted-foreground">Saved.</span>
                </div>

                <div class="pt-6 border-t space-y-4">
                    <h2 class="text-lg font-semibold">Mailer settings</h2>
                    <label class="space-y-1 text-sm">
                        <span>Mailer driver</span>
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
                        <input type="password" v-model="form['mailer.password']" class="w-full rounded-md border px-3 py-2" />
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

                <div class="pt-6 border-t space-y-4">
                    <h2 class="text-lg font-semibold">SMS settings</h2>
                    <label class="space-y-1 text-sm">
                        <span>Provider</span>
                        <input v-model="form['sms.provider']" class="w-full rounded-md border px-3 py-2" placeholder="twilio" />
                    </label>
                    <label class="space-y-1 text-sm">
                        <span>API key</span>
                        <input v-model="form['sms.api_key']" class="w-full rounded-md border px-3 py-2" />
                    </label>
                    <label class="space-y-1 text-sm">
                        <span>Sender ID</span>
                        <input v-model="form['sms.sender_id']" class="w-full rounded-md border px-3 py-2" />
                    </label>
                </div>

                <div class="pt-6 border-t space-y-4">
                    <h2 class="text-lg font-semibold">Payment gateway</h2>
                    <label class="space-y-1 text-sm">
                        <span>Provider</span>
                        <input v-model="form['payment.provider']" class="w-full rounded-md border px-3 py-2" placeholder="stripe" />
                    </label>
                    <label class="space-y-1 text-sm">
                        <span>Public key</span>
                        <input v-model="form['payment.public_key']" class="w-full rounded-md border px-3 py-2" />
                    </label>
                    <label class="space-y-1 text-sm">
                        <span>Secret key</span>
                        <input type="password" v-model="form['payment.secret_key']" class="w-full rounded-md border px-3 py-2" />
                    </label>
                    <label class="space-y-1 text-sm">
                        <span>Webhook secret</span>
                        <input type="password" v-model="form['payment.webhook_secret']" class="w-full rounded-md border px-3 py-2" />
                    </label>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
