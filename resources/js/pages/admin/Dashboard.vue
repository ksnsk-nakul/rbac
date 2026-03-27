<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

type AdminUser = { id: number; name: string; email: string; role?: string };
type ActivityLog = { id: number; email?: string; event: string; role?: string; ip_address?: string; created_at?: string };

defineProps<{
    usersCount: number | null;
    rolesCount: number;
    permissionsCount: number;
    pendingApprovalsCount: number;
    openSupportTicketsCount: number;
    adminUsers: AdminUser[];
    recentActivity: ActivityLog[];
    canRoles: boolean;
    canUsers: boolean;
    canActivityAll: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Admin Dashboard" />

        <div class="space-y-8 p-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold">Admin Dashboard</h1>
                    <p class="text-sm text-muted-foreground">
                        System overview for accounts, roles, permissions, and security activity.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Button v-if="canUsers" as-child variant="secondary">
                        <a href="/admin/management/users">Manage users</a>
                    </Button>
                    <Button v-if="canRoles" as-child>
                        <a href="/admin/management/roles">Manage roles</a>
                    </Button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-xs text-muted-foreground">Total users</div>
                    <div class="mt-3 text-2xl font-semibold">{{ usersCount ?? 0 }}</div>
                    <div class="mt-2 text-xs text-muted-foreground">All registered accounts</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-xs text-muted-foreground">Roles</div>
                    <div class="mt-3 text-2xl font-semibold">{{ rolesCount }}</div>
                    <div class="mt-2 text-xs text-muted-foreground">Access profiles</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-xs text-muted-foreground">Permissions</div>
                    <div class="mt-3 text-2xl font-semibold">{{ permissionsCount }}</div>
                    <div class="mt-2 text-xs text-muted-foreground">Granular capabilities</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-xs text-muted-foreground">Approvals</div>
                    <div class="mt-3 text-2xl font-semibold">{{ pendingApprovalsCount }}</div>
                    <div class="mt-2 text-xs text-muted-foreground">Pending requests</div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">
                <section class="rounded-xl border bg-card p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">Security activity</h2>
                            <p class="text-xs text-muted-foreground">Recent sign-ins and privileged actions.</p>
                        </div>
                        <a v-if="canActivityAll" href="/admin/activity" class="text-sm text-primary hover:underline">View all</a>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b text-xs text-muted-foreground">
                                <tr>
                                    <th class="px-2 py-3 font-medium">Email</th>
                                    <th class="px-2 py-3 font-medium">Event</th>
                                    <th class="px-2 py-3 font-medium">Role</th>
                                    <th class="px-2 py-3 font-medium">IP</th>
                                    <th class="px-2 py-3 font-medium">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in recentActivity" :key="log.id" class="border-b last:border-0">
                                    <td class="px-2 py-3">{{ log.email ?? '—' }}</td>
                                    <td class="px-2 py-3">{{ log.event }}</td>
                                    <td class="px-2 py-3 capitalize">{{ log.role ?? '—' }}</td>
                                    <td class="px-2 py-3">{{ log.ip_address ?? '—' }}</td>
                                    <td class="px-2 py-3">{{ log.created_at ?? '—' }}</td>
                                </tr>
                                <tr v-if="!recentActivity.length">
                                    <td colspan="5" class="px-2 py-8 text-center text-sm text-muted-foreground">
                                        No recent activity.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="rounded-xl border bg-card p-5 space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold">Ops snapshot</h2>
                        <p class="text-xs text-muted-foreground">Support and administrative access.</p>
                    </div>

                    <div class="grid gap-3">
                        <div class="flex items-center justify-between rounded-lg border bg-background px-3 py-2">
                            <div class="text-sm">Open support tickets</div>
                            <Badge variant="secondary">{{ openSupportTicketsCount }}</Badge>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border bg-background px-3 py-2">
                            <div class="text-sm">Super admins</div>
                            <Badge variant="secondary">{{ adminUsers.length }}</Badge>
                        </div>
                    </div>

                    <div v-if="adminUsers.length" class="rounded-lg border">
                        <div class="border-b px-3 py-2 text-sm font-medium">Admin accounts</div>
                        <div class="divide-y">
                            <div v-for="a in adminUsers" :key="a.id" class="px-3 py-2 text-sm">
                                <div class="font-medium">{{ a.name }}</div>
                                <div class="text-xs text-muted-foreground">{{ a.email }}</div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
