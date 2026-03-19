<script setup lang="ts">
import { ref } from 'vue';
import Sidebar from '@/components/Sidebar.vue';
import Topbar from '@/components/Topbar.vue';

const sidebarOpen = ref(false);

const navItems = [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Users', href: '/admin/management/users' },
    { label: 'Roles', href: '/admin/management/roles' },
    { label: 'Activity', href: '/admin/activity' },
    { label: 'Settings', href: '/admin/settings' },
];
</script>

<template>
    <div class="min-h-screen bg-background text-foreground">
        <div class="flex">
            <Sidebar :open="sidebarOpen" :items="navItems" />
            <div class="flex min-h-screen flex-1 flex-col lg:ml-64">
                <Topbar :on-toggle-sidebar="() => (sidebarOpen = !sidebarOpen)" />
                <main class="flex-1 px-4 py-6 md:px-6">
                    <slot />
                </main>
            </div>
        </div>
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-20 bg-black/40 lg:hidden"
            @click="sidebarOpen = false"
        ></div>
    </div>
</template>
