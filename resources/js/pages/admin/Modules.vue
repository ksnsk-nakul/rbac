<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { computed, ref, watch } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

type Module = {
    id: number;
    name: string;
    slug: string;
    enabled: boolean;
    version?: string | null;
    description?: string | null;
    allowed_plans?: string[] | null;
    requires_api_key?: boolean;
    is_addon?: boolean;
};

const props = defineProps<{
    modules: Module[];
    planSlug: string | null;
    hasAddonKey: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/dashboard' },
    { title: 'Modules', href: '/admin/modules' },
];

type PageProps = {
    flash?: {
        addonApiKey?: string | null;
        status?: string | null;
    };
};

const page = usePage<PageProps>();
const flashAddonKey = computed(() => page.props.flash?.addonApiKey ?? null);
const showAddonKeyModal = ref(false);

watch(flashAddonKey, (val) => {
    if (val) {
        showAddonKeyModal.value = true;
    }
});

const installForm = useForm({
    zip: null as File | null,
    force: false,
});

const installAddon = () => {
    installForm.transform((data) => ({
        ...data,
        force: data.force ? 1 : 0,
    }));
    installForm.post('/admin/modules/install', {
        forceFormData: true,
        onSuccess: () => installForm.reset('zip'),
    });
};

const regenerateKeyForm = useForm({});
const regenerateAddonKey = () => {
    regenerateKeyForm.post('/admin/modules/addon-key', { preserveScroll: true });
};

const apiKeyModalOpen = ref(false);
const apiKeyInput = ref('');
const pendingModule = ref<Module | null>(null);

const requestEnableWithKey = (module: Module) => {
    pendingModule.value = module;
    apiKeyInput.value = '';
    apiKeyModalOpen.value = true;
};

const confirmEnableWithKey = () => {
    if (!pendingModule.value) return;
    router.patch(
        `/admin/modules/${pendingModule.value.id}`,
        { enabled: true, api_key: apiKeyInput.value },
        { preserveScroll: true, onFinish: () => (apiKeyModalOpen.value = false) },
    );
};

const toggleModule = (module: Module) => {
    if (!module.enabled && module.requires_api_key) {
        requestEnableWithKey(module);
        return;
    }
    router.patch(`/admin/modules/${module.id}`, { enabled: !module.enabled }, { preserveScroll: true });
};

const uninstallAddon = (module: Module) => {
    if (!module.is_addon) return;
    if (!confirm(`Uninstall ${module.name}? This deletes the module files from disk.`)) return;
    router.delete(`/admin/modules/${module.id}`, { preserveScroll: true });
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

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-lg border p-4 space-y-3">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-sm font-semibold">Add-on API key</h2>
                            <p class="text-xs text-muted-foreground">
                                Required by add-ons that are license-gated. Copy it once and keep it safe.
                            </p>
                        </div>
                        <Button variant="outline" :disabled="regenerateKeyForm.processing" @click="regenerateAddonKey">
                            {{ props.hasAddonKey ? 'Regenerate' : 'Generate' }}
                        </Button>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Current plan: <span class="font-medium text-foreground">{{ props.planSlug ?? 'unknown' }}</span>
                    </p>
                </div>

                <form class="rounded-lg border p-4 space-y-3" @submit.prevent="installAddon">
                    <div>
                        <h2 class="text-sm font-semibold">Install add-on (.zip)</h2>
                        <p class="text-xs text-muted-foreground">
                            Upload a module zip that contains a <code>module.json</code>. The add-on will be installed into <code>modules/</code>.
                        </p>
                    </div>

                    <input
                        type="file"
                        accept=".zip"
                        class="block w-full rounded-md border px-3 py-2 text-sm"
                        @change="installForm.zip = $event.target.files?.[0] || null"
                    />
                    <label class="flex items-center gap-2 text-xs text-muted-foreground">
                        <input type="checkbox" v-model="installForm.force" />
                        Overwrite if module already exists
                    </label>

                    <div class="flex items-center gap-2">
                        <Button type="submit" :disabled="installForm.processing || !installForm.zip">Install</Button>
                        <span v-if="installForm.recentlySuccessful" class="text-xs text-muted-foreground">Installed.</span>
                    </div>
                </form>
            </div>

            <div class="rounded-lg border">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 font-medium">Module</th>
                            <th class="px-4 py-3 font-medium">Version</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Plan</th>
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
                                <span class="text-xs text-muted-foreground">
                                    {{ module.allowed_plans?.length ? module.allowed_plans.join(', ') : 'Any' }}
                                </span>
                                <div v-if="module.requires_api_key" class="text-[11px] text-muted-foreground">
                                    Requires API key
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button
                                        class="rounded-md border px-3 py-1 text-xs hover:bg-muted"
                                        type="button"
                                        @click="toggleModule(module)"
                                    >
                                        {{ module.enabled ? 'Disable' : 'Enable' }}
                                    </button>
                                    <button
                                        v-if="module.is_addon"
                                        class="rounded-md border px-3 py-1 text-xs hover:bg-muted"
                                        type="button"
                                        @click="uninstallAddon(module)"
                                    >
                                        Uninstall
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Dialog :open="showAddonKeyModal" @update:open="showAddonKeyModal = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Add-on API key</DialogTitle>
                    <DialogDescription>
                        Copy this key now. It will not be shown again.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-2">
                    <pre class="rounded-md bg-muted p-3 text-xs overflow-auto">{{ flashAddonKey }}</pre>
                    <div class="flex justify-end gap-2 pt-2">
                        <Button variant="outline" @click="showAddonKeyModal = false">Close</Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog :open="apiKeyModalOpen" @update:open="apiKeyModalOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Enter Add-on API key</DialogTitle>
                    <DialogDescription>
                        This module requires an Add-on API key to enable.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-3">
                    <input
                        v-model="apiKeyInput"
                        class="w-full rounded-md border px-3 py-2 text-sm"
                        placeholder="addon_..."
                    />
                    <div class="flex justify-end gap-2 pt-2">
                        <Button variant="outline" @click="apiKeyModalOpen = false">Cancel</Button>
                        <Button @click="confirmEnableWithKey">Enable</Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
