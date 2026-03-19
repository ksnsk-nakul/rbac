<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

type PageProps = {
    auth?: {
        showTrustDeviceModal?: boolean;
    };
};

const page = usePage<PageProps>();
const isOpen = ref(false);

const shouldPrompt = computed(() => page.props.auth?.showTrustDeviceModal ?? false);

const dismissKey = 'trust_device_dismissed_at';

const canShow = () => {
    const dismissedAt = localStorage.getItem(dismissKey);
    if (!dismissedAt) return true;
    const elapsed = Date.now() - Number(dismissedAt);
    return elapsed > 1000 * 60 * 60 * 6;
};

onMounted(() => {
    if (shouldPrompt.value && canShow()) {
        isOpen.value = true;
    }
});

const trustDevice = () => {
    router.post('/account/settings/security/trusted-devices', {}, {
        preserveScroll: true,
        onFinish: () => {
            isOpen.value = false;
        },
    });
};

const dismiss = () => {
    localStorage.setItem(dismissKey, Date.now().toString());
    isOpen.value = false;
};
</script>

<template>
    <Dialog :open="isOpen" @update:open="isOpen = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Trust this device?</DialogTitle>
                <DialogDescription>
                    You just signed in with MFA. Trusting this device reduces future prompts on this browser.
                </DialogDescription>
            </DialogHeader>
            <div class="flex justify-end gap-2 pt-4">
                <Button variant="outline" @click="dismiss">Not now</Button>
                <Button @click="trustDevice">Trust device</Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
