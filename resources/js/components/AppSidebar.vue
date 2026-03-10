<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Map, ShieldCheck, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

type PageProps = {
    auth?: {
        user?: {
            role?: {
                slug?: string;
            };
        };
        permissions?: string[];
    };
};

const page = usePage<PageProps>();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const roleSlug = computed(() => page.props.auth?.user?.role?.slug);
const dashboardHref = computed(() =>
    roleSlug.value === 'admin' || roleSlug.value === 'subadmin'
        ? '/admin/dashboard'
        : dashboard(),
);
const hasPermission = (permission: string) =>
    permissions.value.includes('*') || permissions.value.includes(permission);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboardHref.value,
            icon: LayoutGrid,
        },
    ];
    if (hasPermission('users.manage')) {
        items.push(
            {
                title: 'Manage users',
                href: '/admin/management/users',
                icon: Users,
            }
        );
    }
    if (hasPermission('subadmins.manage')) {
        items.push({
            title: 'Manage subadmins',
            href: '/admin/management/subadmins',
            icon: Users,
        });
    }
    if (hasPermission('roles.manage')) {
        items.push({
            title: 'Roles & permissions',
            href: '/admin/management/roles',
            icon: ShieldCheck,
        });
    }
    if (hasPermission('sitemap.view')) {
        items.push({
            title: 'Sitemap',
            href: '/admin/management/sitemap',
            icon: Map,
        });
    }
    return items;
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter v-if="footerNavItems.length" :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
