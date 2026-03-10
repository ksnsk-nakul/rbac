<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { useClipboard } from '@vueuse/core';
import { computed } from 'vue';

const portalLabels: Record<string, { title: string; description: string }> = {
    admin: {
        title: 'Admin sign in',
        description: 'Enter your admin credentials to access management',
    },
    subadmin: {
        title: 'Admin sign in',
        description: 'Enter your admin credentials to access management',
    },
};

const props = defineProps<{
    portal: 'admin' | 'subadmin';
    status?: string;
    canResetPassword: boolean;
}>();

const labels = portalLabels[props.portal] ?? portalLabels.admin;
const page = usePage();
const demoCredentials = computed(() => page.props.demoCredentials as
    | {
          admin: { email: string; password: string };
          subadmin: { email: string; password: string };
      }
    | null
    | undefined);
const { copy, copied } = useClipboard();
const showDemo = computed(
    () => !!demoCredentials.value && ['admin', 'subadmin'].includes(props.portal)
);

const tone = computed(() => {
    return 'bg-slate-50 dark:bg-slate-900/40';
});
</script>

<template>
    <AuthBase :title="labels.title" :description="labels.description" :tone="tone">
        <Head :title="labels.title" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <input type="hidden" name="intended_role" :value="portal" />
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
                            :tabindex="5"
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
                    class="mt-4 w-full"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    Sign in
                </Button>
            </div>
        </Form>

        <div
            v-if="showDemo"
            class="mt-6 rounded-lg border border-border bg-muted/30 p-4 text-sm"
        >
            <div class="font-semibold text-foreground">Demo credentials</div>
            <div class="mt-3 grid gap-3">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-muted-foreground">
                            Admin
                        </div>
                        <div class="text-sm text-foreground">
                            {{ demoCredentials?.admin.email }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                            Password: {{ demoCredentials?.admin.password }}
                        </div>
                    </div>
                    <Button
                        type="button"
                        variant="secondary"
                        @click="copy(`Email: ${demoCredentials?.admin.email}\nPassword: ${demoCredentials?.admin.password}`)"
                    >
                        {{ copied ? 'Copied' : 'Copy' }}
                    </Button>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-muted-foreground">
                            Subadmin
                        </div>
                        <div class="text-sm text-foreground">
                            {{ demoCredentials?.subadmin.email }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                            Password: {{ demoCredentials?.subadmin.password }}
                        </div>
                    </div>
                    <Button
                        type="button"
                        variant="secondary"
                        @click="copy(`Email: ${demoCredentials?.subadmin.email}\nPassword: ${demoCredentials?.subadmin.password}`)"
                    >
                        {{ copied ? 'Copied' : 'Copy' }}
                    </Button>
                </div>
            </div>
        </div>
    </AuthBase>
</template>
