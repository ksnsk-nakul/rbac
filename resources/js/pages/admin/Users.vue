<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type User = {
    id: number;
    name: string;
    email: string;
    role?: string | null;
    deleted_at?: string | null;
};

type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    users: Paginated<User>;
}>();

const createForm = useForm({
    name: '',
    email: '',
    password: '',
});

const editingId = ref<number | null>(null);
const editForm = useForm({
    name: '',
    email: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/dashboard' },
    { title: 'Users', href: '/admin/users' },
];

const submitCreate = () => {
    createForm.post('/admin/users', {
        onSuccess: () => createForm.reset('password'),
    });
};

const startEdit = (user: User) => {
    editingId.value = user.id;
    editForm.name = user.name;
    editForm.email = user.email;
};

const cancelEdit = () => {
    editingId.value = null;
};

const submitEdit = () => {
    if (!editingId.value) return;
    editForm.patch(`/admin/users/${editingId.value}`, {
        onSuccess: () => (editingId.value = null),
    });
};

const banUser = (user: User) => router.post(`/admin/users/${user.id}/ban`);
const restoreUser = (user: User) => router.post(`/admin/users/${user.id}/restore`);
const deleteUser = (user: User) => router.delete(`/admin/users/${user.id}`);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Users" />

        <div class="space-y-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Users</h1>
                <p class="text-sm text-muted-foreground">Manage platform accounts.</p>
            </div>

            <form class="rounded-lg border p-4 space-y-3 max-w-xl" @submit.prevent="submitCreate">
                <h2 class="text-lg font-medium">Create user</h2>
                <label class="space-y-1 text-sm">
                    <span>Name</span>
                    <input v-model="createForm.name" class="w-full rounded-md border px-3 py-2" />
                </label>
                <label class="space-y-1 text-sm">
                    <span>Email</span>
                    <input v-model="createForm.email" class="w-full rounded-md border px-3 py-2" />
                </label>
                <label class="space-y-1 text-sm">
                    <span>Password</span>
                    <input v-model="createForm.password" type="password" class="w-full rounded-md border px-3 py-2" />
                </label>
                <button class="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground" type="submit">
                    Create user
                </button>
            </form>

            <div class="rounded-lg border">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Role</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in props.users.data" :key="user.id" class="border-b last:border-0">
                            <td class="px-4 py-3">
                                <div v-if="editingId === user.id" class="space-y-1">
                                    <input v-model="editForm.name" class="w-full rounded-md border px-2 py-1" />
                                </div>
                                <div v-else>{{ user.name }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div v-if="editingId === user.id" class="space-y-1">
                                    <input v-model="editForm.email" class="w-full rounded-md border px-2 py-1" />
                                </div>
                                <div v-else>{{ user.email }}</div>
                            </td>
                            <td class="px-4 py-3 capitalize">{{ user.role ?? 'super_admin' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full border px-2.5 py-1 text-xs">
                                    {{ user.deleted_at ? 'Banned' : 'Active' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-if="editingId !== user.id"
                                        class="rounded-md border px-3 py-1 text-xs hover:bg-muted"
                                        type="button"
                                        @click="startEdit(user)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        v-else
                                        class="rounded-md border px-3 py-1 text-xs hover:bg-muted"
                                        type="button"
                                        @click="submitEdit"
                                    >
                                        Save
                                    </button>
                                    <button
                                        v-if="editingId === user.id"
                                        class="rounded-md border px-3 py-1 text-xs hover:bg-muted"
                                        type="button"
                                        @click="cancelEdit"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        v-if="!user.deleted_at"
                                        class="rounded-md border px-3 py-1 text-xs hover:bg-muted"
                                        type="button"
                                        @click="banUser(user)"
                                    >
                                        Ban
                                    </button>
                                    <button
                                        v-else
                                        class="rounded-md border px-3 py-1 text-xs hover:bg-muted"
                                        type="button"
                                        @click="restoreUser(user)"
                                    >
                                        Restore
                                    </button>
                                    <button
                                        class="rounded-md border px-3 py-1 text-xs text-red-600 hover:bg-muted"
                                        type="button"
                                        @click="deleteUser(user)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
