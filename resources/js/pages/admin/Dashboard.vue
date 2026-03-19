<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { BreadcrumbItem } from '@/types';

type Permission = { id: number; name: string; slug: string };
type Role = { id: number; name: string; slug: string; users_count: number; permissions: Permission[] };
type AdminUser = { id: number; name: string; email: string; role?: string };
type ActivityLog = { id: number; email?: string; event: string; role?: string; ip_address?: string; created_at?: string };

defineProps<{
    roles: Role[];
    permissions: Permission[];
    usersCount: number | null;
    rolesCount: number;
    permissionsCount: number;
    adminUsers: AdminUser[];
    recentActivity: ActivityLog[];
    canRoles: boolean;
    canUsers: boolean;
    canActivityAll: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
];

const inviteForm = reactive({
    name: '',
    email: '',
    role: 'member',
});

const inviteErrors = computed(() => {
    const errors: Record<string, string> = {};
    if (!inviteForm.name.trim()) {
        errors.name = 'Name is required.';
    }
    if (!inviteForm.email.trim()) {
        errors.email = 'Email is required.';
    } else if (!/^\S+@\S+\.\S+$/.test(inviteForm.email)) {
        errors.email = 'Enter a valid email address.';
    }
    return errors;
});

const canSubmitInvite = computed(() => Object.keys(inviteErrors.value).length === 0);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Admin Dashboard" />

        <div class="space-y-8 p-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold">Admin Dashboard</h1>
                    <p class="text-sm text-muted-foreground">
                        Stripe-style overview of revenue, growth, and team activity.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="secondary">Export report</Button>
                    <Button>New workspace</Button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-xs text-muted-foreground">Monthly recurring revenue</div>
                    <div class="mt-3 text-2xl font-semibold">$42,890</div>
                    <div class="mt-2 text-xs text-emerald-600">+12.4% vs last month</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-xs text-muted-foreground">Active subscriptions</div>
                    <div class="mt-3 text-2xl font-semibold">1,238</div>
                    <div class="mt-2 text-xs text-emerald-600">+6.1% vs last month</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-xs text-muted-foreground">Churn rate</div>
                    <div class="mt-3 text-2xl font-semibold">1.8%</div>
                    <div class="mt-2 text-xs text-rose-600">-0.3% vs last month</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-xs text-muted-foreground">New signups</div>
                    <div class="mt-3 text-2xl font-semibold">862</div>
                    <div class="mt-2 text-xs text-emerald-600">+18.2% vs last month</div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">
                <section class="rounded-xl border bg-card p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">Recent invoices</h2>
                            <p class="text-xs text-muted-foreground">Latest billing activity and actions.</p>
                        </div>
                        <Button variant="secondary" size="sm">View all</Button>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b text-xs text-muted-foreground">
                                <tr>
                                    <th class="px-2 py-3 font-medium">Customer</th>
                                    <th class="px-2 py-3 font-medium">Plan</th>
                                    <th class="px-2 py-3 font-medium">Amount</th>
                                    <th class="px-2 py-3 font-medium">Status</th>
                                    <th class="px-2 py-3 text-right font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b last:border-0">
                                    <td class="px-2 py-4">Acme Studios</td>
                                    <td class="px-2 py-4">Growth</td>
                                    <td class="px-2 py-4">$1,200</td>
                                    <td class="px-2 py-4">
                                        <Badge>Paid</Badge>
                                    </td>
                                    <td class="px-2 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="outline" size="sm">View</Button>
                                            <Button size="sm">Refund</Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-b last:border-0">
                                    <td class="px-2 py-4">Riverline</td>
                                    <td class="px-2 py-4">Starter</td>
                                    <td class="px-2 py-4">$199</td>
                                    <td class="px-2 py-4">
                                        <Badge variant="secondary">Pending</Badge>
                                    </td>
                                    <td class="px-2 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="outline" size="sm">Send</Button>
                                            <Button size="sm">Charge</Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-b last:border-0">
                                    <td class="px-2 py-4">Nimbus Health</td>
                                    <td class="px-2 py-4">Enterprise</td>
                                    <td class="px-2 py-4">$9,800</td>
                                    <td class="px-2 py-4">
                                        <Badge variant="secondary">Overdue</Badge>
                                    </td>
                                    <td class="px-2 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="outline" size="sm">Nudge</Button>
                                            <Button size="sm">Collect</Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="rounded-xl border bg-card p-5">
                    <h2 class="text-lg font-semibold">Invite a teammate</h2>
                    <p class="text-xs text-muted-foreground">Add admins or collaborators with scoped access.</p>
                    <form class="mt-4 grid gap-4">
                        <div class="grid gap-2">
                            <Label for="invite-name">Name</Label>
                            <Input id="invite-name" v-model="inviteForm.name" placeholder="Full name" />
                            <InputError :message="inviteErrors.name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="invite-email">Email</Label>
                            <Input id="invite-email" v-model="inviteForm.email" placeholder="name@company.com" />
                            <InputError :message="inviteErrors.email" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="invite-role">Role</Label>
                            <select
                                id="invite-role"
                                v-model="inviteForm.role"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            >
                                <option value="member">Member</option>
                                <option value="admin">Admin</option>
                                <option value="billing">Billing</option>
                            </select>
                        </div>
                        <Button type="button" :disabled="!canSubmitInvite">
                            Send invite
                        </Button>
                        <p v-if="!canSubmitInvite" class="text-xs text-muted-foreground">
                            Complete all fields to send the invitation.
                        </p>
                    </form>
                </section>
            </div>

            <section v-if="canActivityAll && recentActivity.length" class="rounded-xl border bg-card p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Security activity</h2>
                        <p class="text-xs text-muted-foreground">Recent sign-ins and permission changes.</p>
                    </div>
                    <a href="/admin/activity" class="text-sm text-primary hover:underline">View all</a>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs text-muted-foreground">
                            <tr>
                                <th class="px-2 py-3 font-medium">Email</th>
                                <th class="px-2 py-3 font-medium">Event</th>
                                <th class="px-2 py-3 font-medium">Role</th>
                                <th class="px-2 py-3 font-medium">IP</th>
                                <th class="px-2 py-3 font-medium">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in recentActivity" :key="log.id" class="border-b last:border-0">
                                <td class="px-2 py-3">{{ log.email ?? '—' }}</td>
                                <td class="px-2 py-3 capitalize">{{ log.event }}</td>
                                <td class="px-2 py-3 capitalize">{{ log.role ?? '—' }}</td>
                                <td class="px-2 py-3">{{ log.ip_address ?? '—' }}</td>
                                <td class="px-2 py-3">{{ log.created_at ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
