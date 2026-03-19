<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

const props = withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const roles = [
    {
        key: 'user',
        title: 'User',
        description: 'Access general features and your personal dashboard.',
        login: '/user/login',
        register: '/user/register',
    },
    {
        key: 'admin',
        title: 'Admin',
        description: 'Access the management portal.',
        login: '/admin/login',
    },
];
</script>

<template>
    <Head title="Welcome" />
    <div class="min-h-screen bg-background px-6 py-10 text-foreground">
        <div class="mx-auto w-full max-w-5xl">
            <header class="mb-8 flex flex-col gap-2 text-center">
                <h1 class="text-3xl font-semibold">RBAC</h1>
                <p class="text-sm text-muted-foreground">
                    Sign in or register.
                </p>
            </header>

            <div class="grid gap-4 sm:grid-cols-2">
                <div
                    v-for="role in roles"
                    :key="role.key"
                    class="rounded-xl border border-border bg-[color:var(--accent)] p-5 shadow-sm"
                >
                    <div class="text-lg font-semibold">{{ role.title }}</div>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ role.description }}
                    </p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <Link
                            :href="role.login"
                            class="rounded-md bg-primary px-3 py-1.5 text-sm text-primary-foreground"
                        >
                            Sign in
                        </Link>
                        <Link
                            v-if="props.canRegister && role.register"
                            :href="role.register"
                            class="rounded-md border border-primary/30 px-3 py-1.5 text-sm text-primary"
                        >
                            Register
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
