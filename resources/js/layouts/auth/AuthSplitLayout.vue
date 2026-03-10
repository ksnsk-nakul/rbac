<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
    tone?: string;
}>();
</script>

<template>
    <div class="min-h-svh bg-background">
        <div class="grid min-h-svh grid-cols-1 md:grid-cols-2">
            <div class="hidden items-center justify-center p-10 md:flex" :class="tone">
                <div class="max-w-sm text-left">
                    <div class="mb-6 flex items-center gap-3">
                        <AppLogoIcon class="size-10 fill-current text-foreground" />
                        <span class="text-lg font-semibold">RBAC Portal</span>
                    </div>
                    <h1 class="text-3xl font-semibold">{{ title }}</h1>
                    <p class="mt-2 text-sm text-muted-foreground">{{ description }}</p>
                </div>
            </div>
            <div class="flex items-center justify-center p-6 md:p-10">
                <div class="w-full max-w-md">
                    <div class="mb-6 flex justify-end">
                        <AppearanceTabs />
                    </div>
                    <Link :href="home()" class="mb-6 inline-flex items-center gap-2 text-sm font-medium md:hidden">
                        <AppLogoIcon class="size-6 fill-current text-foreground" />
                        <span>RBAC Portal</span>
                    </Link>
                    <div class="mb-6 md:hidden">
                        <h1 class="text-2xl font-semibold">{{ title }}</h1>
                        <p class="mt-2 text-sm text-muted-foreground">{{ description }}</p>
                    </div>
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
