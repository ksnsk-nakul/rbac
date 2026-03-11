<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Permission = { id: number; name: string; slug: string };
type Template = {
    id: number;
    name: string;
    slug: string;
    description?: string | null;
    permissions: Permission[];
};

const props = defineProps<{
    templates: Template[];
    permissions: Permission[];
}>();

const form = useForm({
    name: '',
    slug: '',
    description: '',
    permissions: [] as number[],
});

const applyForm = useForm({
    name: '',
    slug: '',
    route: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Role templates', href: '/admin/management/role-templates' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Role templates" />
        <div class="space-y-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Role templates</h1>
                <p class="text-sm text-muted-foreground">Create reusable permission sets for faster onboarding.</p>
            </div>

            <form class="rounded-lg border p-4 space-y-4" @submit.prevent="form.post('/admin/management/role-templates')">
                <div class="grid gap-2">
                    <Label for="tpl_name">Name</Label>
                    <Input id="tpl_name" v-model="form.name" required />
                </div>
                <div class="grid gap-2">
                    <Label for="tpl_slug">Slug</Label>
                    <Input id="tpl_slug" v-model="form.slug" required />
                </div>
                <div class="grid gap-2">
                    <Label for="tpl_desc">Description</Label>
                    <Input id="tpl_desc" v-model="form.description" />
                </div>
                <div class="grid gap-2">
                    <Label>Permissions</Label>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label v-for="permission in permissions" :key="permission.id" class="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                :value="permission.id"
                                v-model="form.permissions"
                                class="h-4 w-4 rounded border-border"
                            />
                            <span>{{ permission.name }}</span>
                        </label>
                    </div>
                </div>
                <Button :disabled="form.processing">Create template</Button>
            </form>

            <div class="space-y-4">
                <div v-for="template in templates" :key="template.id" class="rounded-lg border p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="text-base font-medium">{{ template.name }}</div>
                            <div class="text-xs text-muted-foreground">{{ template.slug }}</div>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-muted-foreground">{{ template.description ?? 'No description' }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span
                            v-for="permission in template.permissions"
                            :key="permission.id"
                            class="rounded-full border px-2.5 py-1 text-xs"
                        >
                            {{ permission.name }}
                        </span>
                    </div>

                    <form
                        class="mt-4 grid gap-3 sm:grid-cols-3"
                        @submit.prevent="applyForm.post(`/admin/management/role-templates/${template.id}/apply`)"
                    >
                        <div class="grid gap-2">
                            <Label>Role name</Label>
                            <Input v-model="applyForm.name" required />
                        </div>
                        <div class="grid gap-2">
                            <Label>Role slug</Label>
                            <Input v-model="applyForm.slug" required />
                        </div>
                        <div class="grid gap-2">
                            <Label>Role route</Label>
                            <Input v-model="applyForm.route" required />
                        </div>
                        <div class="sm:col-span-3">
                            <Button :disabled="applyForm.processing" variant="outline">Apply template</Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
