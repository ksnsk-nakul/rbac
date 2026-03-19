<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Ticket = {
    id: number;
    subject: string;
    status: string;
    priority: string;
    category: string | null;
    last_message_at: string | null;
    created_at: string | null;
    user: { id: number; name: string; email: string } | null;
};

const props = defineProps<{
    tickets: Ticket[];
    filters: { status: string | null };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/dashboard' },
    { title: 'Support', href: '/admin/support' },
];

const setStatus = (status: string | null) => {
    router.get('/admin/support', status ? { status } : {}, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Support tickets" />

        <div class="space-y-6 p-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Support tickets</h1>
                    <p class="text-sm text-muted-foreground">Inbox for customer requests.</p>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-muted-foreground">Status</label>
                    <select
                        class="rounded-md border px-3 py-2 text-sm"
                        :value="props.filters.status || ''"
                        @change="setStatus(($event.target as HTMLSelectElement).value || null)"
                    >
                        <option value="">All</option>
                        <option value="open">Open</option>
                        <option value="in_progress">In progress</option>
                        <option value="waiting">Waiting</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 text-left">
                        <tr>
                            <th class="px-3 py-2 font-medium">Subject</th>
                            <th class="px-3 py-2 font-medium">Requester</th>
                            <th class="px-3 py-2 font-medium">Status</th>
                            <th class="px-3 py-2 font-medium">Priority</th>
                            <th class="px-3 py-2 font-medium">Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!props.tickets.length" class="border-t">
                            <td class="px-3 py-3 text-muted-foreground" colspan="5">
                                No tickets found.
                            </td>
                        </tr>
                        <tr v-for="t in props.tickets" :key="t.id" class="border-t">
                            <td class="px-3 py-2">
                                <Link class="font-medium hover:underline" :href="`/admin/support/${t.id}`">
                                    {{ t.subject }}
                                </Link>
                                <div v-if="t.category" class="text-xs text-muted-foreground">
                                    {{ t.category }}
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="font-medium">{{ t.user?.name || 'Unknown' }}</div>
                                <div class="text-xs text-muted-foreground">{{ t.user?.email || '' }}</div>
                            </td>
                            <td class="px-3 py-2">{{ t.status }}</td>
                            <td class="px-3 py-2">{{ t.priority }}</td>
                            <td class="px-3 py-2 text-muted-foreground">
                                {{ (t.last_message_at || t.created_at || '').replace('T', ' ').replace('Z', '') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

