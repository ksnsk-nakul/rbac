<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

type Role = {
    id: number;
    name: string;
    route: string;
    is_default: boolean;
};

const props = defineProps<{
    roles: Role[];
    defaultRole: Role | null;
}>();

const defaultRoute = props.defaultRole?.route ?? 'user';
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100"
    >
        <Head title="RBAC Access Management" />

        <section
            class="relative overflow-hidden bg-gradient-to-br from-blue-700 via-blue-600 to-sky-500 text-white"
        >
            <div class="absolute inset-0 opacity-20">
                <div
                    class="absolute top-10 -left-24 h-72 w-72 rounded-full bg-white/30 blur-3xl"
                ></div>
                <div
                    class="absolute right-0 bottom-0 h-80 w-80 rounded-full bg-cyan-300/40 blur-3xl"
                ></div>
            </div>
            <div class="relative mx-auto max-w-6xl px-6 py-16 lg:py-24">
                <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="space-y-6">
                        <p
                            class="text-sm tracking-[0.35em] text-blue-100 uppercase"
                        >
                            Secure Access
                        </p>
                        <h1
                            class="text-4xl leading-tight font-semibold md:text-5xl"
                        >
                            Simplify Access Management with RBAC
                        </h1>
                        <p class="max-w-xl text-base text-blue-100/90">
                            Control user permissions with ease. Assign roles,
                            manage access, and secure your application with a
                            unified dashboard.
                        </p>
                        <div class="flex flex-wrap gap-3">
                            <a
                                :href="`/login/${defaultRoute}`"
                                class="rounded-full bg-white px-6 py-3 text-sm font-semibold text-blue-700 shadow-lg shadow-blue-900/20 transition hover:bg-blue-50"
                            >
                                Get Started
                            </a>
                            <a
                                href="#features"
                                class="rounded-full border border-white/40 px-6 py-3 text-sm font-semibold text-white transition hover:border-white/70"
                            >
                                Explore Features
                            </a>
                        </div>
                    </div>
                    <div class="relative">
                        <div
                            class="rounded-3xl border border-white/20 bg-white/10 p-6 backdrop-blur"
                        >
                            <div class="space-y-4">
                                <div
                                    class="rounded-2xl bg-white/80 p-4 text-slate-900 shadow"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-10 w-10 rounded-full bg-blue-600/20"
                                        ></div>
                                        <div>
                                            <div class="text-sm font-semibold">
                                                Role Insights
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                View who can access what
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="rounded-2xl bg-white/80 p-4 text-slate-900 shadow"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-10 w-10 rounded-full bg-sky-500/20"
                                        ></div>
                                        <div>
                                            <div class="text-sm font-semibold">
                                                Audit Control
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                Track critical changes
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="rounded-2xl bg-white/80 p-4 text-slate-900 shadow"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-10 w-10 rounded-full bg-indigo-500/20"
                                        ></div>
                                        <div>
                                            <div class="text-sm font-semibold">
                                                Team Access
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                Manage your roles quickly
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="mx-auto max-w-6xl px-6 py-14">
            <div class="grid gap-6 md:grid-cols-3">
                <div
                    class="rounded-2xl border bg-white p-6 shadow-sm dark:bg-slate-900"
                >
                    <div class="text-sm font-semibold text-blue-600">
                        Role Management
                    </div>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Create and assign roles with custom permissions.
                    </p>
                </div>
                <div
                    class="rounded-2xl border bg-white p-6 shadow-sm dark:bg-slate-900"
                >
                    <div class="text-sm font-semibold text-blue-600">
                        Access Control
                    </div>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Define who can access what resources.
                    </p>
                </div>
                <div
                    class="rounded-2xl border bg-white p-6 shadow-sm dark:bg-slate-900"
                >
                    <div class="text-sm font-semibold text-blue-600">
                        Audit & Reports
                    </div>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Track key user actions and access logs.
                    </p>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-6 pb-16">
            <div
                class="rounded-3xl border bg-white p-8 text-center shadow-sm dark:bg-slate-900"
            >
                <h2 class="text-2xl font-semibold">
                    Take Control of Your Access Today
                </h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Start with the default role login and explore other role
                    portals as needed.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a
                        :href="`/login/${defaultRoute}`"
                        class="rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow transition hover:bg-blue-700"
                    >
                        Try it Free
                    </a>
                    <a
                        href="#features"
                        class="rounded-full border border-blue-200 px-6 py-3 text-sm font-semibold text-blue-700 transition hover:border-blue-400"
                    >
                        Learn More
                    </a>
                </div>
            </div>
        </section>
    </div>
</template>
