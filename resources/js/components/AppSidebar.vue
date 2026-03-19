<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Activity, LayoutGrid, LifeBuoy, Map, ShieldCheck, Users } from 'lucide-vue-next';
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
const dashboardHref = computed(() => (roleSlug.value === 'super_admin' ? '/admin/dashboard' : dashboard()));
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
    if (hasPermission('accounts.view')) {
        items.push({
            title: 'Manage users',
            href: '/admin/management/users',
            icon: Users,
        });
    }
    if (hasPermission('roles.view')) {
        items.push({
            title: 'Roles & permissions',
            href: '/admin/management/roles',
            icon: ShieldCheck,
        });
    }
    if (hasPermission('audit.view')) {
        items.push({
            title: 'My activity',
            href: '/account/settings/activity',
            icon: Activity,
        });
    }
    if (hasPermission('audit.export')) {
        items.push({
            title: 'Activity (all)',
            href: '/admin/activity',
            icon: Activity,
        });
    }
    if (hasPermission('security.sessions.view')) {
        items.push({
            title: 'Security',
            href: '/admin/security',
            icon: ShieldCheck,
        });
    }
    if (hasPermission('security.ip_allowlist.view')) {
        items.push({
            title: 'IP allowlist',
            href: '/admin/security/allowlist',
            icon: ShieldCheck,
        });
    }
    if (hasPermission('approvals.view')) {
        items.push({
            title: 'Approvals',
            href: '/admin/approvals',
            icon: ShieldCheck,
        });
    }
    if (hasPermission('system.settings.view')) {
        items.push({
            title: 'Sitemap',
            href: '/admin/management/sitemap',
            icon: Map,
        });
    }
    if (hasPermission('system.settings.view')) {
        items.push({
            title: 'App settings',
            href: '/admin/settings',
            icon: ShieldCheck,
        });
    }
    if (hasPermission('integrations.view')) {
        items.push({
            title: 'Integrations',
            href: '/admin/integrations',
            icon: ShieldCheck,
        });
    }
    if (hasPermission('billing.view')) {
        items.push({
            title: 'Billing',
            href: '/admin/billing',
            icon: ShieldCheck,
        });
    }
    if (hasPermission('support.manage')) {
        items.push({
            title: 'Support',
            href: '/admin/support',
            icon: LifeBuoy,
        });
    }
    if (hasPermission('webhooks.view')) {
        items.push({
            title: 'Webhooks',
            href: '/admin/webhooks',
            icon: ShieldCheck,
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
