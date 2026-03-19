<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Search, Menu } from 'lucide-vue-next';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { getInitials } from '@/composables/useInitials';

defineProps<{
    onToggleSidebar?: () => void;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <header class="sticky top-0 z-30 flex h-16 items-center border-b border-border bg-primary px-4 text-white">
        <button
            type="button"
            class="mr-3 inline-flex h-9 w-9 items-center justify-center rounded-md bg-white/10 text-white lg:hidden"
            @click="onToggleSidebar?.()"
        >
            <Menu class="size-4" />
        </button>
        <div class="flex flex-1 items-center gap-3">
            <div class="relative hidden w-80 items-center md:flex">
                <Search class="absolute left-3 size-4 text-white/70" />
                <Input
                    type="search"
                    placeholder="Search..."
                    class="border-white/30 bg-white/10 pl-9 text-white placeholder:text-white/60"
                />
            </div>
        </div>
        <div class="flex items-center gap-3">
            <AppearanceTabs />
            <DropdownMenu>
                <DropdownMenuTrigger class="inline-flex items-center">
                    <Avatar class="size-9 border border-white/30">
                        <AvatarImage :src="user?.avatar" :alt="user?.name" />
                        <AvatarFallback class="bg-white/10 text-white">
                            {{ getInitials(user?.name) }}
                        </AvatarFallback>
                    </Avatar>
                </DropdownMenuTrigger>
                <DropdownMenuContent class="min-w-56" align="end" :side-offset="8">
                    <UserMenuContent v-if="user" :user="user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
