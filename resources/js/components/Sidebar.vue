<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

type NavItem = {
    label: string;
    href: string;
};

const props = defineProps<{
    open: boolean;
    items: NavItem[];
}>();

const page = usePage();
const currentPath = computed(() => page.url);

const isActive = (href: string) =>
    currentPath.value === href || currentPath.value.startsWith(`${href}/`);
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full border-r border-border bg-background transition-transform duration-200 lg:static lg:translate-x-0"
        :class="{ 'translate-x-0': open }"
    >
        <div class="flex h-16 items-center px-6 text-lg font-semibold text-foreground">
            RBAC Starter
        </div>
        <nav class="px-4">
            <Link
                v-for="item in items"
                :key="item.href"
                :href="item.href"
                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition"
                :class="
                    isActive(item.href)
                        ? 'bg-primary text-white'
                        : 'text-muted-foreground hover:bg-orange-100 hover:text-orange-600'
                "
            >
                <span>{{ item.label }}</span>
            </Link>
        </nav>
    </aside>
</template>
