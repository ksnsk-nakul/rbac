<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type ActivityLog = {
    id: number;
    email?: string;
    event: string;
    role?: string;
    ip_address?: string;
    created_at?: string;
};

type PaginatedLogs = {
    data: ActivityLog[];
    current_page: number;
    last_page: number;
    per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    logs: PaginatedLogs;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Activity', href: '/admin/activity' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Activity" />
        <div class="space-y-6 p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold">Activity log</h1>
                    <p class="text-sm text-muted-foreground">Recent account activity across all users.</p>
                </div>
                <div class="flex gap-2">
                    <a
                        href="/admin/audit/export?format=csv"
                        class="rounded border px-3 py-1 text-sm"
                    >
                        Export CSV
                    </a>
                    <a
                        href="/admin/audit/export?format=json"
                        class="rounded border px-3 py-1 text-sm"
                    >
                        Export JSON
                    </a>
                </div>
            </div>
            <div class="rounded-lg border">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Event</th>
                            <th class="px-4 py-3 font-medium">Role</th>
                            <th class="px-4 py-3 font-medium">IP</th>
                            <th class="px-4 py-3 font-medium">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="log in logs.data" :key="log.id" class="border-b last:border-0">
                            <td class="px-4 py-3">{{ log.email ?? '—' }}</td>
                            <td class="px-4 py-3 capitalize">{{ log.event }}</td>
                            <td class="px-4 py-3 capitalize">{{ log.role ?? '—' }}</td>
                            <td class="px-4 py-3">{{ log.ip_address ?? '—' }}</td>
                            <td class="px-4 py-3">{{ log.created_at ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="logs.last_page > 1" class="flex gap-2">
                <Link
                    v-for="link in logs.links.filter((l) => l.url)"
                    :key="link.label"
                    :href="link.url!"
                    class="rounded border px-3 py-1 text-sm"
                    :class="link.active ? 'border-primary bg-muted' : ''"
                    preserve-scroll
                >
                    {{ link.label }}
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
