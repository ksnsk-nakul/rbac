<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Role = {
    id: number;
    name: string;
    slug: string;
    route: string;
    is_default: boolean;
};

const props = defineProps<{
    roles: Role[];
}>();

const page = usePage();
const status = computed(
    () => (page.props.flash as { status?: string } | undefined)?.status,
);

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
            <p
                v-if="status"
                class="rounded-md bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-200"
            >
                {{ status }}
            </p>

            <Heading
                title="Roles & Permissions"
                description="Create roles, assign permissions, and pick the default login role."
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
                        <Input
                            id="name"
                            name="name"
                            required
                            placeholder="Role name"
                        />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="slug">Role slug</Label>
                        <Input
                            id="slug"
                            name="slug"
                            required
                            placeholder="role-slug"
                        />
                        <InputError :message="errors.slug" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="route">Login route</Label>
                        <p class="text-xs text-muted-foreground">
                            Example: login/{route} and register/{route}
                        </p>
                        <Input
                            id="route"
                            name="route"
                            required
                            placeholder="role"
                        />
                        <InputError :message="errors.route" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            id="is_default"
                            name="is_default"
                            type="checkbox"
                            value="1"
                        />
                        <Label for="is_default">Set as default role</Label>
                    </div>
                    <Button type="submit" :disabled="processing"
                        >Create role</Button
                    >
                </Form>
            </section>

            <section class="space-y-4">
                <h3 class="font-medium">Existing roles</h3>
                <div class="rounded-lg border">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 font-medium">Name</th>
                                <th class="px-4 py-3 font-medium">Slug</th>
                                <th class="px-4 py-3 font-medium">Route</th>
                                <th class="px-4 py-3 font-medium">Default</th>
                                <th class="px-4 py-3 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="role in props.roles" :key="role.id" class="border-b last:border-0">
                                <td class="px-4 py-3">{{ role.name }}</td>
                                <td class="px-4 py-3">{{ role.slug }}</td>
                                <td class="px-4 py-3">{{ role.route }}</td>
                                <td class="px-4 py-3">{{ role.is_default ? 'Yes' : 'No' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <Link
                                            :href="`/admin/management/roles/${role.id}/permissions`"
                                            class="rounded border px-3 py-1 text-xs"
                                        >
                                            Permissions
                                        </Link>
                                        <Form
                                            :action="`/admin/management/roles/${role.id}`"
                                            method="post"
                                        >
                                            <input type="hidden" name="_method" value="delete" />
                                            <Button type="submit" variant="destructive" size="sm">
                                                Delete
                                            </Button>
                                        </Form>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!props.roles.length">
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-muted-foreground">
                                    No roles yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
