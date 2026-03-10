<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Management', href: '/admin/management' },
];

const page = usePage();
const permissions = computed(() => (page.props.auth as { permissions?: string[] } | undefined)?.permissions ?? []);
const hasPermission = (permission: string) =>
    permissions.value.includes('*') || permissions.value.includes(permission);

const cards = computed(() => {
    const items: { title: string; description: string; href: string }[] = [];

    if (hasPermission('users.manage')) {
        items.push({
            title: 'Users',
            description: 'Manage user accounts and access.',
            href: '/admin/management/users',
        });
    }

    if (hasPermission('subadmins.manage')) {
        items.push({
            title: 'Subadmins',
            description: 'Create and manage subadmin access.',
            href: '/admin/management/subadmins',
        });
    }

    if (hasPermission('roles.manage')) {
        items.push({
            title: 'Roles & permissions',
            description: 'Create roles and assign permissions.',
            href: '/admin/management/roles',
        });
    }

    if (hasPermission('sitemap.view')) {
        items.push({
            title: 'Sitemap',
            description: 'Overview of admin routes and tools.',
            href: '/admin/management/sitemap',
        });
    }

    return items;
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Management Portal" />
        <div class="space-y-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Management Portal</h1>
                <p class="text-sm text-muted-foreground">
                    Quick access to admin management tools based on your role.
                </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="card in cards"
                    :key="card.title"
                    :href="card.href"
                    class="rounded-lg border border-border p-4 transition hover:border-foreground/40"
                >
                    <div class="text-lg font-medium">{{ card.title }}</div>
                    <p class="mt-1 text-sm text-muted-foreground">{{ card.description }}</p>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
