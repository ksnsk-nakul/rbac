<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type ApprovalRequest = {
    id: number;
    type: string;
    status: string;
    requested_by: number;
    approved_by?: number | null;
    created_at?: string | null;
    payload: Record<string, unknown>;
};

defineProps<{
    requests: ApprovalRequest[];
}>();

const approve = (id: number) => {
    router.post(`/admin/approvals/${id}/approve`);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Approvals', href: '/admin/approvals' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Approvals" />
        <div class="space-y-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Approval requests</h1>
                <p class="text-sm text-muted-foreground">Review high-risk access changes before applying.</p>
            </div>
            <div class="rounded-lg border">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 font-medium">Type</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Requested by</th>
                            <th class="px-4 py-3 font-medium">Created</th>
                            <th class="px-4 py-3 font-medium text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="request in requests" :key="request.id" class="border-b last:border-0">
                            <td class="px-4 py-3">{{ request.type }}</td>
                            <td class="px-4 py-3 capitalize">{{ request.status }}</td>
                            <td class="px-4 py-3">{{ request.requested_by }}</td>
                            <td class="px-4 py-3">{{ request.created_at ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    v-if="request.status === 'pending'"
                                    class="text-sm text-primary hover:underline"
                                    type="button"
                                    @click="approve(request.id)"
                                >
                                    Approve
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!requests.length">
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-muted-foreground">
                                No approval requests.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
