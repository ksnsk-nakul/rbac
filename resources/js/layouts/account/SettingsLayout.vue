<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import type { NavItem } from '@/types';
import { computed } from 'vue';

type PageProps = {
    auth?: {
        permissions?: string[];
    };
};

const { isCurrentOrParentUrl } = useCurrentUrl();
const page = usePage<PageProps>();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const hasPermission = (permission: string) =>
    permissions.value.includes('*') || permissions.value.includes(permission);

const sidebarNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Profile',
            href: '/account/settings/profile',
        },
        {
            title: 'Security',
            href: '/account/settings/security',
        },
    ];

    if (hasPermission('audit.view')) {
        items.push({
            title: 'Activity',
            href: '/account/settings/activity',
        });
    }

    if (hasPermission('security.sessions.view')) {
        items.push({
            title: 'Sessions',
            href: '/account/settings/sessions',
        });
    }

    if (hasPermission('api_tokens.view')) {
        items.push({
            title: 'API tokens',
            href: '/account/settings/api-tokens',
        });
    }

    return items;
});
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            title="Account settings"
            description="Manage your profile, security, and access activity."
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-56">
                <nav class="flex flex-col space-y-1" aria-label="Account settings">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'w-full justify-start',
                            { 'bg-muted': isCurrentOrParentUrl(item.href) },
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component v-if="item.icon" :is="item.icon" class="h-4 w-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
