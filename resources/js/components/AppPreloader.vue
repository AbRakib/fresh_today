<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import { Spinner } from '@/components/ui/spinner';

const isLoading = ref(false);
let showTimer: ReturnType<typeof setTimeout> | undefined;
let unsubscribe: Array<() => void> = [];

const show = () => {
    window.clearTimeout(showTimer);
    showTimer = window.setTimeout(() => {
        isLoading.value = true;
    }, 150);
};

const hide = () => {
    window.clearTimeout(showTimer);
    isLoading.value = false;
};

onMounted(() => {
    unsubscribe = [
        router.on('start', show),
        router.on('finish', hide),
        router.on('cancel', hide),
        router.on('error', hide),
    ];
});

onUnmounted(() => {
    window.clearTimeout(showTimer);
    unsubscribe.forEach((off) => off());
});
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-150 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isLoading"
            class="fixed inset-0 z-50 grid place-items-center bg-background/70 backdrop-blur-[2px]"
            aria-live="polite"
            aria-busy="true"
        >
            <div
                class="flex items-center gap-3 rounded-md border border-border bg-card px-4 py-3 text-sm font-medium text-card-foreground shadow-lg"
            >
                <Spinner class="size-5 text-primary" />
                <span>Loading data...</span>
            </div>
        </div>
    </Transition>
</template>
