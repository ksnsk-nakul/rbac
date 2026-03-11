<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountSettingsLayout from '@/layouts/account/SettingsLayout.vue';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

type SessionEntry = {
    id: string;
    ip_address?: string | null;
    user_agent?: string | null;
    last_activity: number;
    is_current: boolean;
};

defineProps<{
    sessions: SessionEntry[];
}>();

const revoke = (id: string) => {
    router.delete(`/account/settings/sessions/${id}`, { preserveScroll: true });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Account settings', href: '/account/settings/sessions' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Sessions" />

        <AccountSettingsLayout>
            <div class="space-y-3">
                <div>
                    <h2 class="text-lg font-semibold">Active sessions</h2>
                    <p class="text-sm text-muted-foreground">Manage signed-in devices and revoke access.</p>
                </div>
                <div class="rounded-lg border">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 font-medium">Device</th>
                                <th class="px-4 py-3 font-medium">IP</th>
                                <th class="px-4 py-3 font-medium">Location</th>
                                <th class="px-4 py-3 font-medium">Last activity</th>
                                <th class="px-4 py-3 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="session in sessions" :key="session.id" class="border-b last:border-0">
                                <td class="px-4 py-3">
                                    <div class="font-medium">
                                        {{ session.user_agent ?? 'Unknown device' }}
                                    </div>
                                    <div v-if="session.is_current" class="text-xs text-muted-foreground">
                                        Current session
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ session.ip_address ?? '—' }}</td>
                                <td class="px-4 py-3">Unknown</td>
                                <td class="px-4 py-3">{{ new Date(session.last_activity * 1000).toLocaleString() }}</td>
                                <td class="px-4 py-3 text-right">
                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        :disabled="session.is_current"
                                        @click="revoke(session.id)"
                                    >
                                        Revoke
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="!sessions.length">
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-muted-foreground">
                                    No active sessions found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </AccountSettingsLayout>
    </AppLayout>
</template>
