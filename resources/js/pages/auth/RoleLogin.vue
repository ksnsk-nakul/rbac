<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

type RoleLink = {
    id: number;
    name: string;
    route: string;
    is_default: boolean;
};

const props = defineProps<{
    role: string;
    roleName: string;
    isDefault: boolean;
    roles: RoleLink[];
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const tone = 'bg-sky-100/70 text-slate-900 dark:bg-slate-900 dark:text-slate-100';
const otherRoles = props.roles.filter((item) => item.route !== props.role);
</script>

<template>
    <AuthSplitLayout
        :title="`${roleName} Login`"
        description="Sign in to continue to your dashboard."
        :tone="tone"
    >
        <Head :title="`${roleName} Login`" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <input type="hidden" name="intended_role" :value="role" />
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm"
                        >
                            Forgot password?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Remember me</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    :tabindex="4"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    Login
                </Button>
            </div>
        </Form>

        <div v-if="canRegister" class="mt-6 text-center text-sm">
            Need an account?
            <a :href="`/register/${role}`" class="text-primary hover:underline"
                >Register</a
            >
        </div>

        <div
            v-if="isDefault && otherRoles.length"
            class="mt-8 rounded-lg border p-4"
        >
            <div class="text-sm font-medium">Other role portals</div>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <a
                    v-for="item in otherRoles"
                    :key="item.id"
                    :href="`/login/${item.route}`"
                    class="rounded-lg border border-border p-3 text-sm transition hover:border-foreground/40"
                >
                    <div class="font-medium">{{ item.name }}</div>
                    <div class="text-xs text-muted-foreground">
                        Go to {{ item.route }} login
                    </div>
                </a>
            </div>
        </div>
    </AuthSplitLayout>
</template>
