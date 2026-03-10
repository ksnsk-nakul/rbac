<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Permission = { id: number; name: string; slug: string };

type Role = {
    id: number;
    name: string;
    slug: string;
    permissions: Permission[];
};

const props = defineProps<{
    roles: Role[];
    permissions: Permission[];
}>();

const page = usePage();
const status = computed(() => (page.props.flash as { status?: string } | undefined)?.status);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Management', href: '/admin/management' },
    { title: 'Roles & Permissions', href: '/admin/management/roles' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Roles & Permissions" />
        <div class="space-y-8 p-4">
            <p v-if="status" class="rounded-md bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-200">
                {{ status }}
            </p>

            <Heading
                title="Roles & Permissions"
                description="Create roles and assign permissions. Admins have full access by default."
            />

            <section class="space-y-4 rounded-lg border p-4">
                <h3 class="font-medium">Create role</h3>
                <Form
                    action="/admin/management/roles"
                    method="post"
                    class="grid gap-4"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="name">Role name</Label>
                        <Input id="name" name="name" required placeholder="Role name" />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="slug">Role slug</Label>
                        <Input id="slug" name="slug" required placeholder="role-slug" />
                        <InputError :message="errors.slug" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Permissions</Label>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <label
                                v-for="permission in props.permissions"
                                :key="permission.id"
                                class="flex items-center gap-2 rounded-md border px-3 py-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    :value="permission.id"
                                />
                                <span>{{ permission.name }}</span>
                            </label>
                        </div>
                        <InputError :message="errors.permissions" />
                    </div>
                    <Button type="submit" :disabled="processing">Create role</Button>
                </Form>
            </section>

            <section class="space-y-4">
                <h3 class="font-medium">Existing roles</h3>
                <div class="grid gap-4">
                    <div
                        v-for="role in props.roles"
                        :key="role.id"
                        class="rounded-lg border p-4"
                    >
                        <Form
                            :action="`/admin/management/roles/${role.id}`"
                            method="post"
                            class="grid gap-3"
                            v-slot="{ errors, processing }"
                        >
                            <input type="hidden" name="_method" value="put" />
                            <div class="grid gap-2">
                                <Label>Role name</Label>
                                <Input :name="`name`" :default-value="role.name" />
                                <InputError :message="errors.name" />
                            </div>
                            <div class="text-xs text-muted-foreground">Slug: {{ role.slug }}</div>
                            <div class="grid gap-2">
                                <Label>Permissions</Label>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <label
                                        v-for="permission in props.permissions"
                                        :key="permission.id"
                                        class="flex items-center gap-2 rounded-md border px-3 py-2 text-sm"
                                    >
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            :value="permission.id"
                                            :checked="role.permissions.some((p) => p.id === permission.id)"
                                        />
                                        <span>{{ permission.name }}</span>
                                    </label>
                                </div>
                            </div>
                            <Button type="submit" :disabled="processing">Update role</Button>
                        </Form>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
