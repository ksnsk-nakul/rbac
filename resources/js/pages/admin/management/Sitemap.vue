<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Management', href: '/admin/management' },
    { title: 'Sitemap', href: '/admin/management/sitemap' },
];

const sections = [
    {
        title: 'Auth',
        links: [
            { label: 'Role login', href: '/login/{role}' },
            { label: 'Role register', href: '/register/{role}' },
        ],
    },
    {
        title: 'Management',
        links: [
            { label: 'Admin dashboard', href: '/admin/dashboard' },
            { label: 'Activity log', href: '/admin/activity' },
            { label: 'Users', href: '/admin/management/users' },
            { label: 'Subadmins', href: '/admin/management/subadmins' },
            { label: 'Roles & permissions', href: '/admin/management/roles' },
            { label: 'Sitemap', href: '/admin/management/sitemap' },
        ],
    },
    {
        title: 'Personal',
        links: [
            { label: 'My activity', href: '/activity' },
        ],
    },
    {
        title: 'App',
        links: [
            { label: 'Dashboard', href: '/dashboard' },
            { label: 'Home', href: '/' },
        ],
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Sitemap" />
        <div class="space-y-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Sitemap</h1>
                <p class="text-sm text-muted-foreground">
                    Overview of available sections for this deployment.
                </p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <section
                    v-for="section in sections"
                    :key="section.title"
                    class="rounded-lg border p-4"
                >
                    <h2 class="text-lg font-medium">{{ section.title }}</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li v-for="link in section.links" :key="link.href">
                            <a :href="link.href" class="text-primary hover:underline">
                                {{ link.label }}
                            </a>
                        </li>
                    </ul>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
