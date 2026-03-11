<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountSettingsLayout from '@/layouts/account/SettingsLayout.vue';
import type { BreadcrumbItem } from '@/types';

type ApiToken = {
    id: number;
    name: string;
    last_used_at?: string | null;
    created_at?: string | null;
};

const props = defineProps<{
    tokens: ApiToken[];
    newToken?: string | null;
    availableScopes: { id: number; name: string; slug: string }[];
}>();

const form = useForm({
    name: '',
    scopes: [] as string[],
});

const copied = ref(false);

const copyToken = async () => {
    if (!props.newToken) return;
    await navigator.clipboard.writeText(props.newToken);
    copied.value = true;
    setTimeout(() => (copied.value = false), 1500);
};

const destroyForm = useForm({});

const deleteToken = (tokenId: number) => {
    destroyForm.delete(`/account/settings/api-tokens/${tokenId}`, {
        preserveScroll: true,
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Account settings', href: '/account/settings/api-tokens' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="API tokens" />

        <AccountSettingsLayout>
            <div class="space-y-8">
                <div class="space-y-4">
                    <Heading
                        variant="small"
                        title="API tokens"
                        description="Create personal API tokens to access protected endpoints."
                    />

                    <form
                        class="flex flex-col gap-4 rounded-lg border p-4"
                        @submit.prevent="form.post('/account/settings/api-tokens')"
                    >
                        <div class="grid gap-2">
                            <Label for="token_name">Token name</Label>
                            <Input
                                id="token_name"
                                v-model="form.name"
                                placeholder="e.g. CI deploy, Postman, Mobile app"
                                required
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Scopes</Label>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <label
                                    v-for="scope in availableScopes"
                                    :key="scope.id"
                                    class="flex items-center gap-2 text-sm"
                                >
                                    <input
                                        type="checkbox"
                                        :value="scope.slug"
                                        v-model="form.scopes"
                                        class="h-4 w-4 rounded border-border"
                                    />
                                    <span>{{ scope.name }}</span>
                                </label>
                            </div>
                            <InputError :message="form.errors.scopes" />
                        </div>
                        <div>
                            <Button :disabled="form.processing">Create token</Button>
                        </div>
                    </form>

                    <div v-if="newToken" class="rounded-lg border border-dashed p-4">
                        <div class="text-sm font-medium">New token</div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Copy this token now. You will not be able to view it again.
                        </p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <code class="rounded bg-muted px-2 py-1 text-xs">{{ newToken }}</code>
                            <Button size="sm" variant="outline" @click="copyToken">
                                {{ copied ? 'Copied' : 'Copy' }}
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <h2 class="text-lg font-semibold">Active tokens</h2>
                    <div class="rounded-lg border">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Name</th>
                                    <th class="px-4 py-3 font-medium">Last used</th>
                                    <th class="px-4 py-3 font-medium">Created</th>
                                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="token in tokens" :key="token.id" class="border-b last:border-0">
                                    <td class="px-4 py-3">{{ token.name }}</td>
                                    <td class="px-4 py-3">{{ token.last_used_at ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ token.created_at ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <Button
                                            size="sm"
                                            variant="destructive"
                                            :disabled="destroyForm.processing"
                                            @click="deleteToken(token.id)"
                                        >
                                            Revoke
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="!tokens.length">
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-muted-foreground">
                                        No API tokens yet.
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
