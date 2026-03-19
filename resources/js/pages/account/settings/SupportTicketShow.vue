<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
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

const replyForm = useForm({
    message: '',
});

const closeForm = useForm({});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Account settings', href: '/account/settings/profile' },
    { title: 'Support', href: '/account/settings/support' },
    { title: `Ticket #${props.ticket.id}`, href: `/account/settings/support/${props.ticket.id}` },
];

const submitReply = () => {
    replyForm.post(`/account/settings/support/${props.ticket.id}/messages`, {
        onSuccess: () => replyForm.reset('message'),
    });
};

const closeTicket = () => {
    closeForm.patch(`/account/settings/support/${props.ticket.id}/close`);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Support ticket #${ticket.id}`" />

        <AccountSettingsLayout>
            <div class="space-y-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <Heading
                            variant="small"
                            :title="ticket.subject"
                            :description="`Status: ${ticket.status} • Priority: ${ticket.priority}`"
                        />
                        <p class="mt-1 text-xs text-muted-foreground">
                            Ticket #{{ ticket.id }}<span v-if="ticket.category"> • {{ ticket.category }}</span>
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <Button variant="ghost" as-child>
                            <Link href="/account/settings/support">All tickets</Link>
                        </Button>
                        <Button
                            v-if="ticket.status !== 'closed'"
                            variant="destructive"
                            :disabled="closeForm.processing"
                            @click="closeTicket"
                        >
                            Close
                        </Button>
                    </div>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="m in messages"
                        :key="m.id"
                        class="rounded-lg border p-3"
                        :class="m.is_internal ? 'opacity-60' : ''"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-xs text-muted-foreground">
                                <span class="font-medium text-foreground">
                                    {{ m.user?.name || 'System' }}
                                </span>
                                <span v-if="m.user?.email" class="ml-1">({{ m.user.email }})</span>
                                <span v-if="m.is_internal" class="ml-2 rounded bg-muted px-2 py-0.5 text-[10px]">internal</span>
                            </div>
                            <div class="text-[11px] text-muted-foreground">
                                {{ (m.created_at || '').replace('T', ' ').replace('Z', '') }}
                            </div>
                        </div>
                        <div class="mt-2 whitespace-pre-wrap text-sm">
                            {{ m.message }}
                        </div>
                    </div>
                </div>

                <form class="space-y-3 rounded-lg border p-4" @submit.prevent="submitReply">
                    <h3 class="text-sm font-semibold">Reply</h3>
                    <textarea
                        v-model="replyForm.message"
                        class="min-h-28 w-full rounded-md border px-3 py-2"
                        placeholder="Add more details or follow up."
                    />
                    <InputError :message="replyForm.errors.message" />
                    <div class="flex items-center gap-2">
                        <Button type="submit" :disabled="replyForm.processing || ticket.status === 'closed'">Send</Button>
                        <span v-if="replyForm.recentlySuccessful" class="text-xs text-muted-foreground">Sent.</span>
                        <span v-if="ticket.status === 'closed'" class="text-xs text-muted-foreground">Ticket is closed.</span>
                    </div>
                </form>
            </div>
        </AccountSettingsLayout>
    </AppLayout>
</template>

