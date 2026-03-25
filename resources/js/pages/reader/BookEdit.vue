<script setup lang="ts">
import { Form, Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Chapter = { id: number; number: number; title: string; status: string };
type BookFile = { id: number; original_name: string; format: string | null; size: number; download_url: string };
type Book = { id: number; title: string; description: string | null; status: string; chapters: Chapter[]; files: BookFile[] };

const props = defineProps<{
    book: Book | null;
}>();

type PageProps = { auth?: { permissions?: string[] }; flash?: { status?: string } };
const page = usePage<PageProps>();
const status = computed(() => (page.props.flash as { status?: string } | undefined)?.status);
const permissions = computed(() => page.props.auth?.permissions ?? []);
const hasPermission = (permission: string) =>
    permissions.value.includes('*') || permissions.value.includes(permission);

const canManageChapters = computed(() => hasPermission('reader.chapters.manage'));
const canSubmit = computed(() => hasPermission('reader.approval.submit'));
const canUpload = computed(() => hasPermission('reader.books.edit'));

const newChapter = ref({ number: 1, title: '', content: '' });
const uploadForm = useForm<{ file: File | null }>({ file: null });

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Books', href: '/reader/books' },
    { title: props.book ? 'Edit' : 'New', href: props.book ? `/reader/books/${props.book.id}/edit` : '/reader/books/create' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="book ? `Edit · ${book.title}` : 'New book'" />

        <div class="space-y-6 p-4">
            <p
                v-if="status"
                class="rounded-md bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-200"
            >
                {{ status }}
            </p>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <Heading
                    :title="book ? `Edit book` : 'Create book'"
                    description="Update book details and add chapters."
                />
                <Button variant="outline" as-child>
                    <Link href="/reader/books">Back</Link>
                </Button>
            </div>

            <section class="rounded-lg border p-4 space-y-4">
                <h3 class="font-medium">Book details</h3>
                <Form
                    :action="book ? `/reader/books/${book.id}` : '/reader/books'"
                    method="post"
                    class="grid gap-4"
                    v-slot="{ errors, processing }"
                >
                    <input v-if="book" type="hidden" name="_method" value="put" />
                    <div class="grid gap-2">
                        <Label for="title">Title</Label>
                        <Input id="title" name="title" :default-value="book?.title ?? ''" required />
                        <InputError :message="errors.title" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <textarea
                            id="description"
                            name="description"
                            class="min-h-[100px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            :default-value="book?.description ?? ''"
                        />
                        <InputError :message="errors.description" />
                    </div>
                    <Button type="submit" :disabled="processing">{{ book ? 'Save' : 'Create' }}</Button>
                </Form>
            </section>

            <section v-if="book" class="rounded-lg border p-4 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-medium">Chapters</h3>
                        <p class="text-xs text-muted-foreground">Add or update chapter content.</p>
                    </div>
                    <Form
                        v-if="canSubmit && (book.status === 'draft' || book.status === 'rejected')"
                        :action="`/reader/books/${book.id}/submit`"
                        method="post"
                    >
                        <Button type="submit" variant="secondary">Submit for approval</Button>
                    </Form>
                </div>

                <div v-if="!book.chapters.length" class="rounded-md border bg-muted/20 p-6 text-sm text-muted-foreground">
                    No chapters yet.
                </div>

                <div v-else class="space-y-2">
                    <div v-for="chapter in book.chapters" :key="chapter.id" class="flex items-center justify-between rounded-md border p-3">
                        <div>
                            <div class="font-medium">Chapter {{ chapter.number }}: {{ chapter.title }}</div>
                            <div class="text-xs text-muted-foreground capitalize">{{ chapter.status }}</div>
                        </div>
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="`/reader/books/${book.id}`">Open</Link>
                        </Button>
                    </div>
                </div>

                <div v-if="canManageChapters" class="rounded-md border p-4 space-y-3">
                    <h4 class="font-medium">Add chapter</h4>
                    <Form
                        :action="`/reader/books/${book.id}/chapters`"
                        method="post"
                        class="grid gap-4"
                        v-slot="{ errors, processing }"
                    >
                        <div class="grid gap-2">
                            <Label for="number">Number</Label>
                            <Input id="number" name="number" type="number" min="1" :default-value="newChapter.number" required />
                            <InputError :message="errors.number" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="chapter_title">Title</Label>
                            <Input id="chapter_title" name="title" required placeholder="Chapter title" />
                            <InputError :message="errors.title" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="content">Content</Label>
                            <textarea
                                id="content"
                                name="content"
                                class="min-h-[140px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                placeholder="Chapter content"
                            />
                            <InputError :message="errors.content" />
                        </div>
                        <Button type="submit" :disabled="processing">Add chapter</Button>
                    </Form>
                </div>
            </section>

            <section v-if="book" class="rounded-lg border p-4 space-y-4">
                <div>
                    <h3 class="font-medium">Files</h3>
                    <p class="text-xs text-muted-foreground">
                        Upload book files (PDF/DOCX/EPUB/TXT). TXT uploads will create a draft chapter automatically.
                    </p>
                </div>

                <div v-if="!book.files?.length" class="rounded-md border bg-muted/20 p-6 text-sm text-muted-foreground">
                    No files uploaded.
                </div>
                <div v-else class="rounded-md border">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 font-medium">File</th>
                                <th class="px-4 py-3 font-medium">Format</th>
                                <th class="px-4 py-3 font-medium text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="f in book.files" :key="f.id" class="border-b last:border-0">
                                <td class="px-4 py-3">{{ f.original_name }}</td>
                                <td class="px-4 py-3">{{ f.format ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <Button variant="outline" size="sm" as-child>
                                        <a :href="f.download_url">Download</a>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="canUpload" class="rounded-md border p-4 space-y-3">
                    <h4 class="font-medium">Upload file</h4>
                    <form
                        class="grid gap-3"
                        @submit.prevent="
                            uploadForm.post(`/reader/books/${book.id}/files`, {
                                forceFormData: true,
                                preserveScroll: true,
                            })
                        "
                    >
                        <div class="grid gap-2">
                            <Label for="file">File</Label>
                            <input
                                id="file"
                                type="file"
                                class="block w-full text-sm"
                                @change="
                                    (e) => {
                                        const t = e.target as HTMLInputElement;
                                        uploadForm.file = t.files?.[0] ?? null;
                                    }
                                "
                                required
                            />
                            <InputError :message="uploadForm.errors.file" />
                        </div>
                        <Button type="submit" :disabled="uploadForm.processing || !uploadForm.file">Upload</Button>
                    </form>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
