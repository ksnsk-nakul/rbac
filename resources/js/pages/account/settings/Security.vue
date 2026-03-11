<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountSettingsLayout from '@/layouts/account/SettingsLayout.vue';
import type { BreadcrumbItem } from '@/types';

type TrustedDevice = {
    id: number;
    label: string;
    last_used_at?: string | null;
    created_at?: string | null;
};

const props = defineProps<{
    twoFactorEnabled: boolean;
    trustedDevices: TrustedDevice[];
}>();

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const sessionForm = useForm({
    current_password: '',
});

const trustForm = useForm({
    label: 'Trusted device',
});

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Account settings',
        href: '/account/settings/security',
    },
];

const revokeTrustedDevice = (deviceId: number) => {
    router.delete(`/account/settings/security/trusted-devices/${deviceId}`, { preserveScroll: true });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Account security" />

        <AccountSettingsLayout>
            <div class="space-y-10">
                <div class="space-y-6">
                    <Heading
                        variant="small"
                        title="Multi-factor authentication"
                        description="Use an authenticator app to secure your account."
                    />

                    <div class="rounded-lg border p-4 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-medium">
                                MFA status: {{ twoFactorEnabled ? 'Enabled' : 'Disabled' }}
                            </span>
                            <a href="/account/settings/security/mfa" class="text-primary hover:underline">
                                Manage MFA
                            </a>
                        </div>
                        <p class="mt-2 text-muted-foreground">
                            Use a TOTP-compatible authenticator app. Super Admins require MFA.
                        </p>
                    </div>
                </div>

                <div class="space-y-6">
                    <Heading
                        variant="small"
                        title="Update password"
                        description="Use a long, random password to keep your account secure."
                    />

                    <form
                        class="space-y-6"
                        @submit.prevent="passwordForm.put('/account/settings/security/password')"
                    >
                        <div class="grid gap-2">
                            <Label for="current_password">Current password</Label>
                            <Input
                                id="current_password"
                                v-model="passwordForm.current_password"
                                type="password"
                                autocomplete="current-password"
                                placeholder="Current password"
                            />
                            <InputError :message="passwordForm.errors.current_password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password">New password</Label>
                            <Input
                                id="password"
                                v-model="passwordForm.password"
                                type="password"
                                autocomplete="new-password"
                                placeholder="New password"
                            />
                            <InputError :message="passwordForm.errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation">Confirm password</Label>
                            <Input
                                id="password_confirmation"
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                placeholder="Confirm password"
                            />
                            <InputError :message="passwordForm.errors.password_confirmation" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button :disabled="passwordForm.processing">Save password</Button>
                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p v-show="passwordForm.recentlySuccessful" class="text-sm text-neutral-600">
                                    Saved.
                                </p>
                            </Transition>
                        </div>
                    </form>
                </div>

                <div class="space-y-6">
                    <Heading
                        variant="small"
                        title="Logout other sessions"
                        description="Require your current password to log out other devices."
                    />

                    <form
                        class="space-y-4"
                        @submit.prevent="sessionForm.post('/account/settings/security/logout-other-sessions')"
                    >
                        <div class="grid gap-2">
                            <Label for="logout_current_password">Current password</Label>
                            <Input
                                id="logout_current_password"
                                v-model="sessionForm.current_password"
                                type="password"
                                autocomplete="current-password"
                                placeholder="Current password"
                            />
                            <InputError :message="sessionForm.errors.current_password" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button :disabled="sessionForm.processing" variant="outline">
                                Log out other sessions
                            </Button>
                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p v-show="sessionForm.recentlySuccessful" class="text-sm text-neutral-600">
                                    Logged out.
                                </p>
                            </Transition>
                        </div>
                    </form>
                </div>

                <div class="space-y-6">
                    <Heading
                        variant="small"
                        title="Trusted devices"
                        description="Remember devices to reduce MFA prompts."
                    />

                    <form
                        class="flex flex-col gap-3 rounded-lg border p-4"
                        @submit.prevent="trustForm.post('/account/settings/security/trusted-devices')"
                    >
                        <div class="grid gap-2">
                            <Label for="device_label">Device label</Label>
                            <Input id="device_label" v-model="trustForm.label" placeholder="Work laptop" />
                            <InputError :message="trustForm.errors.label" />
                        </div>
                        <Button :disabled="trustForm.processing" variant="outline">Trust this device</Button>
                    </form>

                    <div class="rounded-lg border">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Device</th>
                                    <th class="px-4 py-3 font-medium">Last used</th>
                                    <th class="px-4 py-3 font-medium text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="device in trustedDevices" :key="device.id" class="border-b last:border-0">
                                    <td class="px-4 py-3">{{ device.label }}</td>
                                    <td class="px-4 py-3">{{ device.last_used_at ?? device.created_at ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <Button
                                            size="sm"
                                            variant="destructive"
                                            @click="revokeTrustedDevice(device.id)"
                                        >
                                            Remove
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="!trustedDevices.length">
                                    <td colspan="3" class="px-4 py-6 text-center text-sm text-muted-foreground">
                                        No trusted devices yet.
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
