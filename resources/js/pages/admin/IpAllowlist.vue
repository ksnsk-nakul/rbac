<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { BreadcrumbItem } from '@/types';

type RoleEntry = {
    id: number;
    name: string;
    slug: string;
    require_ip_allowlist: boolean;
    entries: { id: number; ip_address: string; label?: string | null }[];
};

const props = defineProps<{
    roles: RoleEntry[];
}>();

const form = useForm({
    role_id: null as number | null,
    ip_address: '',
    label: '',
});

const toggleForms = new Map<number, ReturnType<typeof useForm>>();

const getToggleForm = (roleId: number, enabled: boolean) => {
    if (!toggleForms.has(roleId)) {
        toggleForms.set(roleId, useForm({ require_ip_allowlist: enabled }));
    }

    return toggleForms.get(roleId)!;
};

const removeEntry = (entryId: number) => {
    router.delete(`/admin/security/allowlist/${entryId}`, { preserveScroll: true });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'IP Allowlist', href: '/admin/security/allowlist' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="IP allowlist" />

        <div class="space-y-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">IP allowlist</h1>
                <p class="text-sm text-muted-foreground">Restrict role access by IP address.</p>
            </div>

            <form
                class="grid gap-4 rounded-lg border p-4 md:grid-cols-3"
                @submit.prevent="form.post('/admin/security/allowlist')"
            >
                <div class="grid gap-2">
                    <Label for="role_id">Role</Label>
                    <select
                        id="role_id"
                        v-model="form.role_id"
                        class="h-10 rounded-md border border-input bg-background px-3 text-sm"
                        required
                    >
                        <option :value="null" disabled>Select role</option>
                        <option v-for="role in roles" :key="role.id" :value="role.id">
                            {{ role.name }}
                        </option>
                    </select>
                </div>
                <div class="grid gap-2">
                    <Label for="ip_address">IP address</Label>
                    <Input id="ip_address" v-model="form.ip_address" placeholder="203.0.113.10" required />
                </div>
                <div class="grid gap-2">
                    <Label for="label">Label</Label>
                    <Input id="label" v-model="form.label" placeholder="Office network" />
                </div>
                <div class="md:col-span-3">
                    <Button :disabled="form.processing">Add entry</Button>
                </div>
            </form>

            <div class="space-y-4">
                <div v-for="role in roles" :key="role.id" class="rounded-lg border p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="text-base font-medium">{{ role.name }}</div>
                            <div class="text-xs text-muted-foreground">{{ role.slug }}</div>
                        </div>
                        <form
                            class="flex items-center gap-2 text-sm"
                            @submit.prevent="
                                getToggleForm(role.id, !role.require_ip_allowlist)
                                    .put(`/admin/security/allowlist/roles/${role.id}`)
                            "
                        >
                            <span>{{ role.require_ip_allowlist ? 'Enforced' : 'Not enforced' }}</span>
                            <Button size="sm" variant="outline">
                                {{ role.require_ip_allowlist ? 'Disable' : 'Enable' }}
                            </Button>
                        </form>
                    </div>
                    <div class="mt-4">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-4 py-2 font-medium">IP</th>
                                    <th class="px-4 py-2 font-medium">Label</th>
                                    <th class="px-4 py-2 font-medium text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="entry in role.entries" :key="entry.id" class="border-b last:border-0">
                                    <td class="px-4 py-2">{{ entry.ip_address }}</td>
                                    <td class="px-4 py-2">{{ entry.label ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <Button
                                            size="sm"
                                            variant="destructive"
                                            @click="removeEntry(entry.id)"
                                        >
                                            Remove
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="!role.entries.length">
                                    <td colspan="3" class="px-4 py-4 text-center text-sm text-muted-foreground">
                                        No allowlist entries yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
