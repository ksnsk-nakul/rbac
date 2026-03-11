<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type EventLog = {
    id: number;
    action: string;
    email?: string | null;
    ip_address?: string | null;
    created_at?: string | null;
};

type FailedLogin = {
    id: number;
    ip_address?: string | null;
    created_at?: string | null;
};

type SessionEntry = {
    id: string;
    user_id: number;
    ip_address?: string | null;
    user_agent?: string | null;
    last_activity: number;
};

defineProps<{
    recentEvents: EventLog[];
    failedLogins: FailedLogin[];
    activeSessions: number;
    recentSessions: SessionEntry[];
    tokenCount: number;
    adminCount: number;
}>();

const revokeSession = (id: string) => {
    router.delete(`/admin/security/sessions/${id}`, { preserveScroll: true });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Security', href: '/admin/security' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Security dashboard" />
        <div class="space-y-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Security dashboard</h1>
                <p class="text-sm text-muted-foreground">Monitor security signals across the platform.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border p-4">
                    <div class="text-sm text-muted-foreground">Active sessions</div>
                    <div class="mt-2 text-2xl font-semibold">{{ activeSessions }}</div>
                </div>
                <div class="rounded-lg border p-4">
                    <div class="text-sm text-muted-foreground">API tokens</div>
                    <div class="mt-2 text-2xl font-semibold">{{ tokenCount }}</div>
                </div>
                <div class="rounded-lg border p-4">
                    <div class="text-sm text-muted-foreground">Admins</div>
                    <div class="mt-2 text-2xl font-semibold">{{ adminCount }}</div>
                </div>
                <div class="rounded-lg border p-4">
                    <div class="text-sm text-muted-foreground">Failed logins</div>
                    <div class="mt-2 text-2xl font-semibold">{{ failedLogins.length }}</div>
                </div>
            </div>

            <section class="space-y-3">
                <h2 class="text-lg font-medium">Recent security events</h2>
                <div class="rounded-lg border">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 font-medium">Action</th>
                                <th class="px-4 py-3 font-medium">User</th>
                                <th class="px-4 py-3 font-medium">IP</th>
                                <th class="px-4 py-3 font-medium">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="event in recentEvents" :key="event.id" class="border-b last:border-0">
                                <td class="px-4 py-3">{{ event.action }}</td>
                                <td class="px-4 py-3">{{ event.email ?? '—' }}</td>
                                <td class="px-4 py-3">{{ event.ip_address ?? '—' }}</td>
                                <td class="px-4 py-3">{{ event.created_at ?? '—' }}</td>
                            </tr>
                            <tr v-if="!recentEvents.length">
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-muted-foreground">
                                    No security events yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-medium">Failed login attempts</h2>
                <div class="rounded-lg border">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 font-medium">IP</th>
                                <th class="px-4 py-3 font-medium">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="attempt in failedLogins" :key="attempt.id" class="border-b last:border-0">
                                <td class="px-4 py-3">{{ attempt.ip_address ?? '—' }}</td>
                                <td class="px-4 py-3">{{ attempt.created_at ?? '—' }}</td>
                            </tr>
                            <tr v-if="!failedLogins.length">
                                <td colspan="2" class="px-4 py-6 text-center text-sm text-muted-foreground">
                                    No failed login attempts.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-medium">Active sessions</h2>
                <div class="rounded-lg border">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 font-medium">User ID</th>
                                <th class="px-4 py-3 font-medium">Device</th>
                                <th class="px-4 py-3 font-medium">IP</th>
                                <th class="px-4 py-3 font-medium">Last activity</th>
                                <th class="px-4 py-3 font-medium text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="session in recentSessions" :key="session.id" class="border-b last:border-0">
                                <td class="px-4 py-3">{{ session.user_id }}</td>
                                <td class="px-4 py-3">{{ session.user_agent ?? 'Unknown' }}</td>
                                <td class="px-4 py-3">{{ session.ip_address ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    {{ new Date(session.last_activity * 1000).toLocaleString() }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        class="text-sm text-destructive hover:underline"
                                        type="button"
                                        @click="revokeSession(session.id)"
                                    >
                                        Revoke
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!recentSessions.length">
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-muted-foreground">
                                    No active sessions found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
