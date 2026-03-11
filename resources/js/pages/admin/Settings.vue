<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    values: Record<string, string | null>;
}>();

const form = useForm({
    ...props.values,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/dashboard' },
    { title: 'Settings', href: '/admin/settings' },
];

const submit = () => {
    form.patch('/admin/settings');
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
                    <span>Support email</span>
                    <input v-model="form['system.support_email']" class="w-full rounded-md border px-3 py-2" />
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
