<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Chapter = { id: number; number: number; title: string; content: string | null; status: string };
type BookFile = { id: number; original_name: string; format: string | null; size: number; download_url: string };
type Book = {
    id: number;
    title: string;
    description: string | null;
    status: string;
    is_copyrighted: boolean;
    author: { id: number; name: string } | null;
};

const props = defineProps<{
    book: Book;
    chapters: Chapter[];
    files: BookFile[];
}>();

const selectedId = ref<number | null>(props.chapters[0]?.id ?? null);
const currentChapter = computed(() => props.chapters.find((c) => c.id === selectedId.value) ?? null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Books', href: '/reader/books' },
    { title: props.book.title, href: `/reader/books/${props.book.id}` },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="book.title" />

        <div class="p-4 space-y-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="space-y-1">
                    <Heading :title="book.title" :description="book.description ?? '—'" />
                    <div class="text-xs text-muted-foreground">
                        <span class="capitalize">{{ book.status }}</span>
                        <span v-if="book.author"> · {{ book.author.name }}</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/reader/books/${book.id}/edit`">Edit</Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/reader/books">Back</Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-[320px,1fr]">
                <aside class="rounded-lg border p-4 space-y-3">
                    <div class="text-sm font-medium">Chapters</div>
                    <div v-if="!chapters.length" class="text-sm text-muted-foreground">No chapters.</div>
                    <button
                        v-for="c in chapters"
                        :key="c.id"
                        type="button"
                        class="w-full rounded-md border px-3 py-2 text-left text-sm hover:bg-muted"
                        :class="selectedId === c.id ? 'border-primary bg-primary/10' : ''"
                        @click="selectedId = c.id"
                    >
                        <div class="font-medium">#{{ c.number }} {{ c.title }}</div>
                        <div class="text-xs text-muted-foreground capitalize">{{ c.status }}</div>
                    </button>
                </aside>

                <main class="rounded-lg border p-6">
                    <div v-if="!currentChapter" class="text-sm text-muted-foreground">
                        Select a chapter to read.
                    </div>
                    <div v-else class="space-y-4">
                        <h2 class="text-xl font-semibold">
                            Chapter {{ currentChapter.number }}: {{ currentChapter.title }}
                        </h2>
                        <div class="prose prose-neutral max-w-none dark:prose-invert">
                            <p v-if="!currentChapter.content" class="text-muted-foreground">
                                No content yet.
                            </p>
                            <div v-else v-html="currentChapter.content"></div>
                        </div>
                    </div>
                </main>
            </div>

            <section v-if="files.length" class="rounded-lg border p-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium">Downloads</div>
                    <div class="text-xs text-muted-foreground">
                        Downloads are available when approved (or to editors).
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <Button v-for="f in files" :key="f.id" variant="outline" size="sm" as-child>
                        <a :href="f.download_url">{{ f.original_name }}</a>
                    </Button>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
