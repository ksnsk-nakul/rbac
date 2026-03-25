<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
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
}>();

type PageProps = { auth?: { permissions?: string[] }; flash?: { status?: string } };
const page = usePage<PageProps>();
const status = computed(() => (page.props.flash as { status?: string } | undefined)?.status);
const permissions = computed(() => page.props.auth?.permissions ?? []);
const hasPermission = (permission: string) =>
    permissions.value.includes('*') || permissions.value.includes(permission);

const canCreate = computed(() => hasPermission('reader.books.create'));
const canDelete = computed(() => hasPermission('reader.books.delete'));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Books', href: '/reader/books' },
];

function destroy(id: number) {
    if (!canDelete.value) return;
    if (!confirm('Delete this book?')) return;
    router.delete(`/reader/books/${id}`, { preserveScroll: true });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Books" />

        <div class="space-y-6 p-4">
            <p
                v-if="status"
                class="rounded-md bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-200"
            >
                {{ status }}
            </p>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <Heading title="Books" description="Manage books and chapters for your organization." />
                <Button v-if="canCreate" as-child>
                    <Link href="/reader/books/create">New book</Link>
                </Button>
            </div>

            <div v-if="!props.books.length" class="rounded-lg border p-10 text-center">
                <div class="text-lg font-medium">No books yet</div>
                <p class="mt-2 text-sm text-muted-foreground">
                    Create your first book to start writing chapters.
                </p>
            </div>

            <div v-else class="rounded-lg border">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 font-medium">Title</th>
                            <th class="px-4 py-3 font-medium">Author</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="book in props.books" :key="book.id" class="border-b last:border-0">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ book.title }}</div>
                                <div class="text-xs text-muted-foreground">/{{ book.slug }}</div>
                            </td>
                            <td class="px-4 py-3">{{ book.author?.name ?? '—' }}</td>
                            <td class="px-4 py-3 capitalize">{{ book.status }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="`/reader/books/${book.id}`">Read</Link>
                                    </Button>
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="`/reader/books/${book.id}/edit`">Edit</Link>
                                    </Button>
                                    <Button
                                        v-if="canDelete"
                                        variant="destructive"
                                        size="sm"
                                        @click="destroy(book.id)"
                                    >
                                        Delete
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

