<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Module = {
    id: number;
    name: string;
    slug: string;
    enabled: boolean;
    version?: string | null;
    description?: string | null;
};

const props = defineProps<{
    modules: Module[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/dashboard' },
    { title: 'Modules', href: '/admin/modules' },
];

const toggleModule = (module: Module) => {
    router.patch(`/admin/modules/${module.id}`, { enabled: !module.enabled });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Modules" />

        <div class="space-y-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Modules</h1>
                <p class="text-sm text-muted-foreground">Enable or disable installed modules.</p>
            </div>

            <div class="rounded-lg border">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 font-medium">Module</th>
                            <th class="px-4 py-3 font-medium">Version</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="module in props.modules" :key="module.id" class="border-b last:border-0">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ module.name }}</div>
                                <div class="text-xs text-muted-foreground">{{ module.description ?? module.slug }}</div>
                            </td>
                            <td class="px-4 py-3">{{ module.version ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full border px-2.5 py-1 text-xs">
                                    {{ module.enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    class="rounded-md border px-3 py-1 text-xs hover:bg-muted"
                                    type="button"
                                    @click="toggleModule(module)"
                                >
                                    {{ module.enabled ? 'Disable' : 'Enable' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
