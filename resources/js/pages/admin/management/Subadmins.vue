<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type User = {
    id: number;
    name: string;
    email: string;
    role?: { id: number; name: string; slug: string };
};

type PaginatedUsers = {
    data: User[];
    current_page: number;
    last_page: number;
    per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    users: PaginatedUsers;
    roleLabel: string;
}>();

const page = usePage();
const status = computed(() => (page.props.flash as { status?: string } | undefined)?.status);
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Management', href: '/admin/management/subadmins' },
    { title: props.roleLabel, href: '/admin/management/subadmins' },
];

function destroy(userId: number) {
    if (!confirm('Remove this subadmin? They will not be able to sign in or register again.')) return;
    router.delete(`/admin/management/subadmins/${userId}`, { preserveScroll: true });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Manage ${roleLabel}`" />
        <div class="space-y-8 p-4">
            <p v-if="status" class="rounded-md bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-200">
                {{ status }}
            </p>
            <Heading
                :title="roleLabel"
                description="Subadmins are managed here. Add new subadmins or remove existing ones. Removed subadmins cannot sign in again."
            />

            <section class="space-y-4 rounded-lg border p-4">
                <h3 class="font-medium">Add subadmin</h3>
                <Form
                    action="/admin/management/subadmins"
                    method="post"
                    class="flex flex-wrap items-end gap-4"
                    :preserve-scroll="true"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" name="name" required placeholder="Name" />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input id="email" name="email" type="email" required placeholder="email@example.com" />
                        <InputError :message="errors.email" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input id="password" name="password" type="password" required placeholder="••••••••" />
                        <InputError :message="errors.password" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirm password</Label>
                        <Input id="password_confirmation" name="password_confirmation" type="password" required placeholder="••••••••" />
                    </div>
                    <Button type="submit" :disabled="processing">Add subadmin</Button>
                </Form>
            </section>

            <section class="space-y-4">
                <h3 class="font-medium">Existing subadmins</h3>
                <div class="rounded-md border">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 font-medium">Name</th>
                                <th class="px-4 py-3 font-medium">Email</th>
                                <th class="w-[100px] px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data" :key="user.id" class="border-b last:border-0">
                                <td class="px-4 py-3">{{ user.name }}</td>
                                <td class="px-4 py-3">{{ user.email }}</td>
                                <td class="px-4 py-3">
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="destroy(user.id)"
                                    >
                                        Remove
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="users.last_page > 1" class="flex gap-2">
                    <Link
                        v-for="link in users.links.filter((l) => l.url)"
                        :key="link.label"
                        :href="link.url!"
                        class="rounded border px-3 py-1 text-sm"
                        :class="link.active ? 'border-primary bg-muted' : ''"
                        preserve-scroll
                    >
                        {{ link.label }}
                    </Link>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
