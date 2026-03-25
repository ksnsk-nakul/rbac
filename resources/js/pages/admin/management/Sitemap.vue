<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Management', href: '/admin/management' },
    { title: 'Sitemap', href: '/admin/management/sitemap' },
];

type LinkItem = { label: string; href: string; permission?: string };

type PageProps = {
    auth?: {
        permissions?: string[];
    };
};

const page = usePage<PageProps>();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const hasPermission = (permission: string) =>
    permissions.value.includes('*') || permissions.value.includes(permission);
const roleSlug = computed(() => (page.props.auth as { user?: { role?: { slug?: string } } } | undefined)?.user?.role?.slug);

const sections = computed(() => [
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
            { label: 'Admin dashboard', href: '/admin/dashboard', permission: 'dashboard.read' },
            { label: 'Activity log', href: '/admin/activity', permission: 'audit.export' },
            { label: 'Users', href: '/admin/management/users', permission: 'accounts.view' },
            { label: 'Roles & permissions', href: '/admin/management/roles', permission: 'roles.view' },
            { label: 'Sitemap', href: '/admin/management/sitemap', permission: 'system.settings.view' },
            // Subadmins route/page is optional; keep hidden unless your app enables it.
            { label: 'Subadmins', href: '/admin/management/subadmins', permission: 'accounts.update' },
        ],
    },
    {
        title: 'Personal',
        links: [
            { label: 'My activity', href: '/account/settings/activity', permission: 'audit.view' },
        ],
    },
    {
        title: 'App',
        links: [
            { label: 'Dashboard', href: '/dashboard', permission: 'dashboard.read' },
            { label: 'Home', href: '/' },
        ],
    },
].map((section) => ({
    ...section,
    links: (section.links as LinkItem[])
        .filter((link) => !link.permission || hasPermission(link.permission))
        .filter((link) => (link.href === '/admin/activity' ? roleSlug.value === 'super_admin' : true)),
})).filter((section) => section.links.length > 0));
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
