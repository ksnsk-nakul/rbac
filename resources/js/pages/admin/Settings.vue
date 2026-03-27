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
            </form>
        </div>
    </AppLayout>
</template>
