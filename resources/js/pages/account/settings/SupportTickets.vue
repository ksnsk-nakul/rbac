<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountSettingsLayout from '@/layouts/account/SettingsLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Ticket = {
    id: number;
    subject: string;
    status: string;
    priority: string;
    category: string | null;
    last_message_at: string | null;
    created_at: string | null;
};

const props = defineProps<{
    tickets: Ticket[];
}>();

const form = useForm({
    subject: '',
    category: 'access' as string | null,
    priority: 'normal' as 'low' | 'normal' | 'high' | 'urgent',
    message: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Account settings', href: '/account/settings/profile' },
    { title: 'Support', href: '/account/settings/support' },
];

const submit = () => {
    form.post('/account/settings/support', {
        onSuccess: () => form.reset('subject', 'message'),
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Support" />

        <AccountSettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Support"
                    description="Create a ticket and track responses."
                />

                <form class="space-y-4 rounded-lg border p-4" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="subject">Subject</Label>
                        <Input id="subject" v-model="form.subject" placeholder="I can't access my account" />
                        <InputError :message="form.errors.subject" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label class="space-y-1 text-sm">
                            <span>Category</span>
                            <select v-model="form.category" class="w-full rounded-md border px-3 py-2">
                                <option value="access">Access</option>
                                <option value="billing">Billing</option>
                                <option value="bug">Bug</option>
                                <option value="other">Other</option>
                            </select>
                            <InputError :message="form.errors.category" />
                        </label>
                        <label class="space-y-1 text-sm">
                            <span>Priority</span>
                            <select v-model="form.priority" class="w-full rounded-md border px-3 py-2">
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                            <InputError :message="form.errors.priority" />
                        </label>
                    </div>

                    <div class="grid gap-2">
                        <Label for="message">Message</Label>
                        <textarea
                            id="message"
                            v-model="form.message"
                            class="min-h-28 w-full rounded-md border px-3 py-2"
                            placeholder="Describe the issue and include steps to reproduce."
                        />
                        <InputError :message="form.errors.message" />
                    </div>

                    <div class="flex items-center gap-2">
                        <Button type="submit" :disabled="form.processing">Create ticket</Button>
                        <span v-if="form.recentlySuccessful" class="text-xs text-muted-foreground">Created.</span>
                    </div>
                </form>

                <div class="space-y-2">
                    <h2 class="text-sm font-semibold">Your tickets</h2>

                    <div class="overflow-hidden rounded-lg border">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 text-left">
                                <tr>
                                    <th class="px-3 py-2 font-medium">Subject</th>
                                    <th class="px-3 py-2 font-medium">Status</th>
                                    <th class="px-3 py-2 font-medium">Priority</th>
                                    <th class="px-3 py-2 font-medium">Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!props.tickets.length" class="border-t">
                                    <td class="px-3 py-3 text-muted-foreground" colspan="4">
                                        No tickets yet.
                                    </td>
                                </tr>
                                <tr v-for="t in props.tickets" :key="t.id" class="border-t">
                                    <td class="px-3 py-2">
                                        <Link class="font-medium hover:underline" :href="`/account/settings/support/${t.id}`">
                                            {{ t.subject }}
                                        </Link>
                                    </td>
                                    <td class="px-3 py-2">{{ t.status }}</td>
                                    <td class="px-3 py-2">{{ t.priority }}</td>
                                    <td class="px-3 py-2">
                                        <span class="text-muted-foreground">
                                            {{ (t.last_message_at || t.created_at || '').replace('T', ' ').replace('Z', '') }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AccountSettingsLayout>
    </AppLayout>
</template>

