<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
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
const isSubadmin = computed(() => (page.props.auth as { user?: { role?: { slug?: string } } } | undefined)?.user?.role?.slug === 'subadmin');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Management', href: '/admin/management/users' },
    { title: props.roleLabel, href: '/admin/management/users' },
];

function destroy(userId: number) {
    const warning = isSubadmin.value
        ? 'Remove this user? This will ban them from signing in or registering again.'
        : 'Remove this user? They will not be able to sign in or register again.';
    if (!confirm(warning)) return;
    router.delete(`/admin/management/users/${userId}`, { preserveScroll: true });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Manage ${roleLabel}`" />
        <div class="space-y-6 p-4">
            <p v-if="status" class="rounded-md bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-200">
                {{ status }}
            </p>
            <Heading
                :title="roleLabel"
                :description="`View and remove ${roleLabel.toLowerCase()}. Removed users cannot sign in or register again.`"
            />
            <p
                v-if="isSubadmin"
                class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-500/40 dark:bg-amber-950/30 dark:text-amber-100"
            >
                Warning: Removing a user bans them from signing in or registering again.
            </p>
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
        </div>
    </AppLayout>
</template>
