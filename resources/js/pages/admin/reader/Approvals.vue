<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Book = {
    id: number;
    title: string;
    slug: string;
    status: string;
    is_copyrighted: boolean;
    author: { id: number; name: string } | null;
    created_at?: string | null;
};

const props = defineProps<{
    books: Book[];
    filters: { status: string };
}>();

type PageProps = { flash?: { status?: string } };
const page = usePage<PageProps>();
const statusMsg = computed(() => (page.props.flash as { status?: string } | undefined)?.status);

const status = ref(props.filters.status ?? 'submitted');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Reader approvals', href: '/admin/reader/approvals' },
];

function apply() {
    router.get('/admin/reader/approvals', { status: status.value }, { preserveScroll: true, preserveState: true });
}

function approve(bookId: number) {
    router.post(`/admin/reader/approvals/${bookId}/approve`, {}, { preserveScroll: true });
}

function reject(bookId: number) {
    router.post(`/admin/reader/approvals/${bookId}/reject`, {}, { preserveScroll: true });
}

function markCopyright(bookId: number) {
    router.post(`/admin/reader/approvals/${bookId}/copyright`, {}, { preserveScroll: true });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Reader approvals" />

        <div class="space-y-6 p-4">
            <p
                v-if="statusMsg"
                class="rounded-md bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-200"
            >
                {{ statusMsg }}
            </p>

            <Heading title="Reader approvals" description="Approve or reject submitted books." />

            <div class="flex flex-wrap items-end gap-3 rounded-lg border p-4">
                <label class="grid gap-1 text-sm">
                    <span>Status</span>
                    <select v-model="status" class="h-9 rounded-md border border-input bg-background px-2 text-sm">
                        <option value="submitted">Submitted</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="draft">Draft</option>
                    </select>
                </label>
                <Button type="button" @click="apply">Apply</Button>
            </div>

            <div v-if="!books.length" class="rounded-lg border p-10 text-center">
                <div class="text-lg font-medium">No books</div>
                <p class="mt-2 text-sm text-muted-foreground">No books found for the selected status.</p>
            </div>

            <div v-else class="rounded-lg border">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 font-medium">Title</th>
                            <th class="px-4 py-3 font-medium">Author</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Copyright</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="b in books" :key="b.id" class="border-b last:border-0">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ b.title }}</div>
                                <div class="text-xs text-muted-foreground">/{{ b.slug }}</div>
                            </td>
                            <td class="px-4 py-3">{{ b.author?.name ?? '—' }}</td>
                            <td class="px-4 py-3 capitalize">{{ b.status }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs" :class="b.is_copyrighted ? 'text-emerald-600' : 'text-muted-foreground'">
                                    {{ b.is_copyrighted ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        v-if="b.status === 'submitted'"
                                        size="sm"
                                        @click="approve(b.id)"
                                    >
                                        Approve
                                    </Button>
                                    <Button
                                        v-if="b.status === 'submitted'"
                                        variant="destructive"
                                        size="sm"
                                        @click="reject(b.id)"
                                    >
                                        Reject
                                    </Button>
                                    <Button
                                        v-if="b.status === 'approved' && !b.is_copyrighted"
                                        variant="outline"
                                        size="sm"
                                        @click="markCopyright(b.id)"
                                    >
                                        Mark copyrighted
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

