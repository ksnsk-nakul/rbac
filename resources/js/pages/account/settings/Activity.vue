<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountSettingsLayout from '@/layouts/account/SettingsLayout.vue';
import type { BreadcrumbItem } from '@/types';

type LoginLog = {
    id: number;
    event: string;
    ip_address?: string;
    created_at?: string;
};

type ActionLog = {
    id: number;
    action: string;
    description?: string | null;
    ip_address?: string | null;
    created_at?: string | null;
};

defineProps<{
    loginLogs: LoginLog[];
    actionLogs: ActionLog[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Account settings', href: '/account/settings/activity' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Account activity" />

        <AccountSettingsLayout>
            <div class="space-y-8">
                <section class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold">Login activity</h2>
                        <p class="text-sm text-muted-foreground">Recent sign-in events for your account.</p>
                    </div>
                    <div class="rounded-lg border">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Event</th>
                                    <th class="px-4 py-3 font-medium">IP</th>
                                    <th class="px-4 py-3 font-medium">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in loginLogs" :key="log.id" class="border-b last:border-0">
                                    <td class="px-4 py-3 capitalize">{{ log.event }}</td>
                                    <td class="px-4 py-3">{{ log.ip_address ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ log.created_at ?? '—' }}</td>
                                </tr>
                                <tr v-if="!loginLogs.length">
                                    <td colspan="3" class="px-4 py-6 text-center text-sm text-muted-foreground">
                                        No login activity yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold">Account actions</h2>
                        <p class="text-sm text-muted-foreground">Security and access events tied to your account.</p>
                    </div>
                    <div class="rounded-lg border">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Action</th>
                                    <th class="px-4 py-3 font-medium">Details</th>
                                    <th class="px-4 py-3 font-medium">IP</th>
                                    <th class="px-4 py-3 font-medium">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in actionLogs" :key="log.id" class="border-b last:border-0">
                                    <td class="px-4 py-3">{{ log.action }}</td>
                                    <td class="px-4 py-3">{{ log.description ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ log.ip_address ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ log.created_at ?? '—' }}</td>
                                </tr>
                                <tr v-if="!actionLogs.length">
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-muted-foreground">
                                        No account actions yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </AccountSettingsLayout>
    </AppLayout>
</template>
