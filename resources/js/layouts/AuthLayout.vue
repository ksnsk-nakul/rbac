<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps<{
    title?: string;
    description?: string;
    tone?: string;
}>();
</script>

<template>
    <AuthLayout :title="title" :description="description" :tone="tone">
        <slot />
    </AuthLayout>
</template>
