<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Permission = { id: number; name: string; slug: string };
type Role = { id: number; name: string; slug: string; users_count: number; permissions: Permission[] };
type AdminUser = { id: number; name: string; email: string; role?: string };

const props = defineProps<{
    roles: Role[];
    permissions: Permission[];
    usersCount: number | null;
    adminUsers: AdminUser[];
    canRoles: boolean;
    canUsers: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Admin Dashboard" />

        <div class="space-y-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Admin Dashboard</h1>
                <p class="text-sm text-muted-foreground">
                    Overview of users, roles, permissions, and admin accounts.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-lg border p-4">
                    <div class="text-sm text-muted-foreground">User accounts</div>
                    <div class="mt-2 text-2xl font-semibold">
                        {{ usersCount ?? '—' }}
                    </div>
                    <div v-if="!canUsers" class="mt-2 text-xs text-muted-foreground">
                        You do not have access to user metrics.
                    </div>
                </div>
                <div class="rounded-lg border p-4">
                    <div class="text-sm text-muted-foreground">Roles</div>
                    <div class="mt-2 text-2xl font-semibold">
                        {{ roles.length || '—' }}
                    </div>
                    <div v-if="!canRoles" class="mt-2 text-xs text-muted-foreground">
                        You do not have access to role data.
                    </div>
                </div>
                <div class="rounded-lg border p-4">
                    <div class="text-sm text-muted-foreground">Permissions</div>
                    <div class="mt-2 text-2xl font-semibold">
                        {{ permissions.length || '—' }}
                    </div>
                </div>
            </div>

            <section v-if="adminUsers.length" class="space-y-3">
                <h2 class="text-lg font-medium">Admin accounts</h2>
                <div class="rounded-lg border">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 font-medium">Name</th>
                                <th class="px-4 py-3 font-medium">Email</th>
                                <th class="px-4 py-3 font-medium">Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="account in adminUsers" :key="account.id" class="border-b last:border-0">
                                <td class="px-4 py-3">{{ account.name }}</td>
                                <td class="px-4 py-3">{{ account.email }}</td>
                                <td class="px-4 py-3 capitalize">{{ account.role ?? 'admin' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section v-if="roles.length" class="space-y-3">
                <h2 class="text-lg font-medium">Roles & permissions</h2>
                <div class="space-y-3">
                    <div v-for="role in roles" :key="role.id" class="rounded-lg border p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <div class="text-base font-medium">{{ role.name }}</div>
                                <div class="text-xs text-muted-foreground">{{ role.slug }}</div>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                Users: {{ role.users_count }}
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span
                                v-for="permission in role.permissions"
                                :key="permission.id"
                                class="rounded-full border px-2.5 py-1 text-xs"
                            >
                                {{ permission.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="permissions.length" class="space-y-3">
                <h2 class="text-lg font-medium">All permissions</h2>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="permission in permissions"
                        :key="permission.id"
                        class="rounded-full border px-2.5 py-1 text-xs"
                    >
                        {{ permission.name }}
                    </span>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
