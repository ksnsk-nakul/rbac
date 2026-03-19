<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Ticket = {
    id: number;
    subject: string;
    status: string;
    priority: string;
    category: string | null;
    last_message_at: string | null;
    created_at: string | null;
    user: { id: number; name: string; email: string } | null;
};

type Message = {
    id: number;
    message: string;
    is_internal: boolean;
    user: { id: number; name: string; email: string } | null;
    created_at: string | null;
};

const props = defineProps<{
    ticket: Ticket;
    messages: Message[];
}>();

const messageForm = useForm({
    message: '',
    is_internal: false,
});

const updateForm = useForm({
    status: props.ticket.status,
    priority: props.ticket.priority,
    category: props.ticket.category || '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/dashboard' },
    { title: 'Support', href: '/admin/support' },
    { title: `Ticket #${props.ticket.id}`, href: `/admin/support/${props.ticket.id}` },
];

const sendMessage = () => {
    messageForm.post(`/admin/support/${props.ticket.id}/messages`, {
        onSuccess: () => messageForm.reset('message', 'is_internal'),
    });
};

const updateTicket = () => {
    updateForm.patch(`/admin/support/${props.ticket.id}`, { preserveScroll: true });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Support ticket #${ticket.id}`" />

        <div class="space-y-6 p-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold">{{ ticket.subject }}</h1>
                    <p class="text-sm text-muted-foreground">
                        Ticket #{{ ticket.id }}
                        <span v-if="ticket.user"> • {{ ticket.user.name }} ({{ ticket.user.email }})</span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Link class="rounded-md border px-3 py-2 text-sm hover:bg-muted" href="/admin/support">
                        Back to inbox
                    </Link>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <form class="space-y-3 rounded-lg border p-4" @submit.prevent="updateTicket">
                    <h2 class="text-sm font-semibold">Ticket settings</h2>

                    <label class="space-y-1 text-sm">
                        <span>Status</span>
                        <select v-model="updateForm.status" class="w-full rounded-md border px-3 py-2">
                            <option value="open">Open</option>
                            <option value="in_progress">In progress</option>
                            <option value="waiting">Waiting</option>
                            <option value="closed">Closed</option>
                        </select>
                    </label>

                    <label class="space-y-1 text-sm">
                        <span>Priority</span>
                        <select v-model="updateForm.priority" class="w-full rounded-md border px-3 py-2">
                            <option value="low">Low</option>
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </label>

                    <label class="space-y-1 text-sm">
                        <span>Category</span>
                        <input v-model="updateForm.category" class="w-full rounded-md border px-3 py-2" placeholder="billing / access / bug" />
                    </label>

                    <div class="flex items-center gap-2">
                        <button class="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground" type="submit" :disabled="updateForm.processing">
                            Save
                        </button>
                        <span v-if="updateForm.recentlySuccessful" class="text-xs text-muted-foreground">Saved.</span>
                    </div>
                </form>

                <div class="space-y-3 rounded-lg border p-4 lg:col-span-2">
                    <h2 class="text-sm font-semibold">Conversation</h2>
                    <div class="space-y-3">
                        <div
                            v-for="m in messages"
                            :key="m.id"
                            class="rounded-lg border p-3"
                            :class="m.is_internal ? 'opacity-60' : ''"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-xs text-muted-foreground">
                                    <span class="font-medium text-foreground">{{ m.user?.name || 'System' }}</span>
                                    <span v-if="m.user?.email" class="ml-1">({{ m.user.email }})</span>
                                    <span v-if="m.is_internal" class="ml-2 rounded bg-muted px-2 py-0.5 text-[10px]">internal</span>
                                </div>
                                <div class="text-[11px] text-muted-foreground">
                                    {{ (m.created_at || '').replace('T', ' ').replace('Z', '') }}
                                </div>
                            </div>
                            <div class="mt-2 whitespace-pre-wrap text-sm">{{ m.message }}</div>
                        </div>
                    </div>

                    <form class="mt-4 space-y-3 border-t pt-4" @submit.prevent="sendMessage">
                        <textarea
                            v-model="messageForm.message"
                            class="min-h-28 w-full rounded-md border px-3 py-2"
                            placeholder="Reply to the customer..."
                        />
                        <div class="flex items-center justify-between gap-2">
                            <label class="flex items-center gap-2 text-xs text-muted-foreground">
                                <input type="checkbox" v-model="messageForm.is_internal" />
                                Internal note
                            </label>
                            <div class="flex items-center gap-2">
                                <button class="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground" type="submit" :disabled="messageForm.processing">
                                    Send
                                </button>
                                <span v-if="messageForm.recentlySuccessful" class="text-xs text-muted-foreground">Sent.</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

