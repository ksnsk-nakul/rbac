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

type Permission = { id: number; name: string; slug: string; main_group: string };
type Role = {
    id: number;
    name: string;
    slug: string;
    route: string;
    is_default: boolean;
    is_protected: boolean;
    permissions: Permission[];
};

const props = defineProps<{
    role: Role;
    permissions: Permission[];
}>();

const page = usePage();
const status = computed(() => (page.props.flash as { status?: string } | undefined)?.status);
const permissionsList = computed(() => (page.props.auth as { permissions?: string[] } | undefined)?.permissions ?? []);
const hasPermission = (permission: string) =>
    permissionsList.value.includes('*') || permissionsList.value.includes(permission);

const canEditRole = computed(() => hasPermission('roles.edit') && props.role.slug !== 'super_admin');
const canAssignPermissions = computed(() => hasPermission('permissions.assign') && props.role.slug !== 'super_admin');
const isSuperAdmin = computed(() => props.role.slug === 'super_admin');
const isProtected = computed(() => props.role.is_protected && props.role.slug !== 'super_admin');

const grouped = computed(() => {
    const groups: Record<string, Permission[]> = {};
    props.permissions.forEach((permission) => {
        groups[permission.main_group] ??= [];
        groups[permission.main_group].push(permission);
    });
    return groups;
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Roles', href: '/admin/management/roles' },
    { title: props.role.name, href: `/admin/management/roles/${props.role.id}/permissions` },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Permissions · ${role.name}`" />
        <div class="space-y-8 p-4">
            <p
                v-if="status"
                class="rounded-md bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-200"
            >
                {{ status }}
            </p>

            <Heading
                :title="`Role: ${role.name}`"
                description="Update role details and assign permissions."
            />

            <section class="space-y-4 rounded-lg border p-4">
                <h3 class="font-medium">Role details</h3>
                <p v-if="isSuperAdmin" class="text-xs text-muted-foreground">
                    Super Admin is a protected role. It cannot be edited or deleted, and it implicitly has all permissions.
                </p>
                <p v-else-if="isProtected" class="text-xs text-muted-foreground">
                    This role is protected from deletion. You may still update its name/login route.
                </p>
                <Form
                    :action="`/admin/management/roles/${role.id}`"
                    method="post"
                    class="grid gap-4"
                    v-slot="{ errors, processing }"
                >
                    <input type="hidden" name="_method" value="put" />
                    <div class="grid gap-2">
                        <Label for="name">Role name</Label>
                        <Input id="name" name="name" :default-value="role.name" required :disabled="!canEditRole" />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="slug">Role slug</Label>
                        <Input id="slug" name="slug" :default-value="role.slug" required :disabled="!canEditRole" />
                        <InputError :message="errors.slug" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="route">Login route</Label>
                        <Input id="route" name="route" :default-value="role.route" required :disabled="!canEditRole" />
                        <InputError :message="errors.route" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            id="is_default"
                            name="is_default"
                            type="checkbox"
                            value="1"
                            :checked="role.is_default"
                            :disabled="!canEditRole"
                        />
                        <Label for="is_default">Default role</Label>
                    </div>
                    <Button type="submit" :disabled="processing || !canEditRole">Save role</Button>
                </Form>
            </section>

            <section class="space-y-4 rounded-lg border p-4">
                <h3 class="font-medium">Permissions</h3>
                <Form
                    :action="`/admin/management/roles/${role.id}/permissions`"
                    method="post"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <input type="hidden" name="_method" value="put" />
                    <div v-for="(items, group) in grouped" :key="group" class="space-y-2">
                        <div class="text-sm font-medium uppercase text-muted-foreground">{{ group }}</div>
                        <div class="flex flex-wrap gap-2">
                            <label
                                v-for="permission in items"
                                :key="permission.id"
                                class="flex items-center gap-2 rounded-full border px-3 py-1 text-xs"
                            >
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    :value="permission.id"
                                    :checked="role.permissions.some((p) => p.id === permission.id)"
                                    :disabled="!canAssignPermissions"
                                />
                                {{ permission.name }}
                            </label>
                        </div>
                    </div>
                    <InputError :message="errors.permissions" />
                    <Button type="submit" :disabled="processing || !canAssignPermissions">Save permissions</Button>
                </Form>
            </section>
        </div>
    </AppLayout>
</template>
