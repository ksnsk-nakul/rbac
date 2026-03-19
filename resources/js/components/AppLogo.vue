<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

type PageProps = {
    appSettings?: {
        app_name?: string | null;
        app_logo?: string | null;
    };
};

const page = usePage<PageProps>();
const appName = computed(() => page.props.appSettings?.app_name || 'Laravel Starter Kit');
const appLogo = computed(() => page.props.appSettings?.app_logo);
</script>

<template>
    <div class="flex items-center gap-2">
        <div
            class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
        >
            <img
                v-if="appLogo"
                :src="`/storage/${appLogo}`"
                alt="App logo"
                class="size-6 rounded object-contain"
            />
            <AppLogoIcon v-else class="size-5 fill-current text-white dark:text-black" />
        </div>
        <div class="grid flex-1 text-left text-sm">
            <span class="mb-0.5 truncate leading-tight font-semibold">
                {{ appName }}
            </span>
        </div>
    </div>
</template>
