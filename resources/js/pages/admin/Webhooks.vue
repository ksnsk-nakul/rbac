<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { BreadcrumbItem } from '@/types';

type Webhook = {
    id: number;
    name: string;
    url: string;
    active: boolean;
    events: string[];
};

const props = defineProps<{
    endpoints: Webhook[];
    events: string[];
}>();

const form = useForm({
    name: '',
    url: '',
    secret: '',
    events: [] as string[],
});

const remove = (id: number) => {
    router.delete(`/admin/webhooks/${id}`, { preserveScroll: true });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Webhooks', href: '/admin/webhooks' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Webhooks" />
        <div class="space-y-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Security webhooks</h1>
                <p class="text-sm text-muted-foreground">Send security events to external systems.</p>
            </div>

            <form class="rounded-lg border p-4 space-y-4" @submit.prevent="form.post('/admin/webhooks')">
                <div class="grid gap-2">
                    <Label for="hook_name">Name</Label>
                    <Input id="hook_name" v-model="form.name" required />
                </div>
                <div class="grid gap-2">
                    <Label for="hook_url">URL</Label>
                    <Input id="hook_url" v-model="form.url" required />
                </div>
                <div class="grid gap-2">
                    <Label for="hook_secret">Secret (optional)</Label>
                    <Input id="hook_secret" v-model="form.secret" />
                </div>
                <div class="grid gap-2">
                    <Label>Events</Label>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label v-for="event in events" :key="event" class="flex items-center gap-2 text-sm">
                            <input type="checkbox" :value="event" v-model="form.events" class="h-4 w-4 rounded border-border" />
                            <span>{{ event }}</span>
                        </label>
                    </div>
                </div>
                <Button :disabled="form.processing">Add webhook</Button>
            </form>

            <div class="rounded-lg border">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">URL</th>
                            <th class="px-4 py-3 font-medium">Events</th>
                            <th class="px-4 py-3 font-medium text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="hook in endpoints" :key="hook.id" class="border-b last:border-0">
                            <td class="px-4 py-3">{{ hook.name }}</td>
                            <td class="px-4 py-3">{{ hook.url }}</td>
                            <td class="px-4 py-3">{{ hook.events.join(', ') }}</td>
                            <td class="px-4 py-3 text-right">
                                <Button size="sm" variant="destructive" @click="remove(hook.id)">Delete</Button>
                            </td>
                        </tr>
                        <tr v-if="!endpoints.length">
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-muted-foreground">
                                No webhook endpoints yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
