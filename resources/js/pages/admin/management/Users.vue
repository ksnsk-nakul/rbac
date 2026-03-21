<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
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
    filters: { q: string; role: string | null; status: string; view: string };
}>();

const page = usePage();
const status = computed(() => (page.props.flash as { status?: string } | undefined)?.status);
const assignments = reactive<Record<number, { role_id: number | null; expires_at: string | null }>>({});
const q = ref(props.filters.q ?? '');
const role = ref(props.filters.role ?? '');
const filterStatus = ref(props.filters.status ?? 'all');
const view = ref(props.filters.view ?? 'list');
const isGrid = computed(() => view.value === 'grid');

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

function applyFilters() {
    router.get(
        '/admin/management/users',
        {
            q: q.value || undefined,
            role: role.value || undefined,
            status: filterStatus.value !== 'all' ? filterStatus.value : undefined,
            view: view.value,
        },
        { preserveScroll: true, preserveState: true }
    );
}

function setView(v: 'list' | 'grid') {
    view.value = v;
    applyFilters();
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

            <div class="flex flex-col gap-3 rounded-lg border p-4 md:flex-row md:items-end md:justify-between">
                <div class="grid gap-3 md:grid-cols-3 md:items-end">
                    <label class="space-y-1 text-sm">
                        <span>Search</span>
                        <input
                            v-model="q"
                            class="w-full rounded-md border px-3 py-2"
                            placeholder="Search name or email"
                            @keydown.enter.prevent="applyFilters"
                        />
                    </label>
                    <label class="space-y-1 text-sm">
                        <span>Status</span>
                        <select v-model="filterStatus" class="w-full rounded-md border px-3 py-2" @change="applyFilters">
                            <option value="all">All</option>
                            <option value="active">Active</option>
                            <option value="banned">Banned</option>
                        </select>
                    </label>
                    <label class="space-y-1 text-sm">
                        <span>Role</span>
                        <select v-model="role" class="w-full rounded-md border px-3 py-2" @change="applyFilters">
                            <option value="">All</option>
                            <option v-for="r in roles" :key="r.id" :value="r.slug">{{ r.name }}</option>
                        </select>
                    </label>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        class="rounded-md border px-3 py-2 text-xs hover:bg-muted"
                        type="button"
                        :class="isGrid ? '' : 'bg-muted'"
                        @click="setView('list')"
                    >
                        List
                    </button>
                    <button
                        class="rounded-md border px-3 py-2 text-xs hover:bg-muted"
                        type="button"
                        :class="isGrid ? 'bg-muted' : ''"
                        @click="setView('grid')"
                    >
                        Grid
                    </button>
                    <button
                        class="rounded-md bg-primary px-3 py-2 text-xs text-primary-foreground"
                        type="button"
                        @click="applyFilters"
                    >
                        Apply
                    </button>
                </div>
            </div>

            <div v-if="!isGrid" class="rounded-md border">
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

            <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="user in users.data" :key="user.id" class="rounded-lg border p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-semibold">{{ user.name }}</div>
                            <div class="text-xs text-muted-foreground">{{ user.email }}</div>
                        </div>
                        <span class="rounded-full border px-2.5 py-1 text-xs">
                            {{ user.role?.slug ?? '—' }}
                        </span>
                    </div>

                    <form class="flex flex-wrap items-center gap-2" @submit.prevent="submitAssignment(user.id)">
                        <select
                            v-model="assignments[user.id].role_id"
                            class="h-9 rounded-md border border-input bg-background px-2 text-sm"
                        >
                            <option :value="null" disabled>Select role</option>
                            <option v-for="r in roles" :key="r.id" :value="r.id">
                                {{ r.name }}
                            </option>
                        </select>
                        <input
                            type="date"
                            v-model="assignments[user.id].expires_at"
                            class="h-9 rounded-md border border-input bg-background px-2 text-sm"
                        />
                        <Button size="sm" variant="outline" type="submit">Assign</Button>
                    </form>

                    <div class="pt-2">
                        <Button variant="destructive" size="sm" @click="destroy(user.id)">Remove</Button>
                    </div>
                </div>
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
