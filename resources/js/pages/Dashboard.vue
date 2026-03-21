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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

const form = reactive({
    name: '',
    email: '',
    role: 'member',
});

const errors = computed(() => {
    const next: Record<string, string> = {};
    if (!form.name.trim()) {
        next.name = 'Name is required.';
    }
    if (!form.email.trim()) {
        next.email = 'Email is required.';
    } else if (!/^\S+@\S+\.\S+$/.test(form.email)) {
        next.email = 'Enter a valid email address.';
    }
    return next;
});

const canSubmit = computed(() => Object.keys(errors.value).length === 0);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Dashboard" />

        <div class="space-y-8">
            <div>
                <h1 class="text-2xl font-semibold">Overview</h1>
                <p class="text-sm text-muted-foreground">
                    RBAC overview and recent platform activity.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
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
                    <div class="text-xs text-muted-foreground">Trial conversions</div>
                    <div class="mt-3 text-2xl font-semibold">8.4%</div>
                    <div class="mt-2 text-xs text-emerald-600">+1.2% vs last month</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-xs text-muted-foreground">Churn rate</div>
                    <div class="mt-3 text-2xl font-semibold">1.8%</div>
                    <div class="mt-2 text-xs text-rose-600">-0.3% vs last month</div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">
                <section class="rounded-xl border bg-card p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">Recent transactions</h2>
                            <p class="text-xs text-muted-foreground">Latest subscription payments.</p>
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
                    <h2 class="text-lg font-semibold">Invite teammate</h2>
                    <p class="text-xs text-muted-foreground">Give teammates access quickly.</p>
                    <form class="mt-4 grid gap-4">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" placeholder="Full name" />
                            <InputError :message="errors.name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" placeholder="name@company.com" />
                            <InputError :message="errors.email" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="role">Role</Label>
                            <select
                                id="role"
                                v-model="form.role"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            >
                                <option value="member">Member</option>
                                <option value="admin">Admin</option>
                                <option value="billing">Billing</option>
                            </select>
                        </div>
                        <Button type="button" :disabled="!canSubmit">Send invite</Button>
                        <p v-if="!canSubmit" class="text-xs text-muted-foreground">
                            Complete all fields to send the invitation.
                        </p>
                    </form>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
