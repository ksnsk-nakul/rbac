<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
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

type Role = { id: number; name: string; slug: string };

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
    roles: Role[];
}>();

const page = usePage();
const status = computed(() => (page.props.flash as { status?: string } | undefined)?.status);
const assignments = reactive<Record<number, { role_id: number | null; expires_at: string | null }>>({});

props.users.data.forEach((user) => {
    assignments[user.id] = {
        role_id: user.role?.id ?? null,
        expires_at: null,
    };
});
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Management', href: '/admin/management/users' },
    { title: props.roleLabel, href: '/admin/management/users' },
];

function destroy(userId: number) {
    if (!confirm('Remove this user? They will not be able to sign in or register again.')) return;
    router.delete(`/admin/management/users/${userId}`, { preserveScroll: true });
}

function submitAssignment(userId: number) {
    const assignment = assignments[userId];
    if (!assignment?.role_id) return;
    router.post(
        `/admin/management/users/${userId}/assign-role`,
        {
            role_id: assignment.role_id,
            expires_at: assignment.expires_at || null,
        },
        { preserveScroll: true }
    );
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
            <div class="rounded-md border">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Temporary role</th>
                            <th class="w-[100px] px-4 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users.data" :key="user.id" class="border-b last:border-0">
                            <td class="px-4 py-3">{{ user.name }}</td>
                            <td class="px-4 py-3">{{ user.email }}</td>
                            <td class="px-4 py-3">
                                <form class="flex flex-wrap items-center gap-2" @submit.prevent="submitAssignment(user.id)">
                                    <select
                                        v-model="assignments[user.id].role_id"
                                        class="h-9 rounded-md border border-input bg-background px-2 text-sm"
                                    >
                                        <option :value="null" disabled>Select role</option>
                                        <option v-for="role in roles" :key="role.id" :value="role.id">
                                            {{ role.name }}
                                        </option>
                                    </select>
                                    <input
                                        type="date"
                                        v-model="assignments[user.id].expires_at"
                                        class="h-9 rounded-md border border-input bg-background px-2 text-sm"
                                    />
                                    <Button size="sm" variant="outline" type="submit">Assign</Button>
                                </form>
                            </td>
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
