<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

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
            class="fixed inset-0 z-50 grid place-items-center bg-white/85"
            aria-live="polite"
            aria-busy="true"
            aria-label="Loading"
        >
            <div class="page-preloader" role="status">
                <span
                    v-for="dot in 8"
                    :key="dot"
                    class="page-preloader__dot"
                    :style="{ '--dot-index': dot - 1 }"
                />
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.page-preloader {
    position: relative;
    width: 46px;
    height: 46px;
}

.page-preloader__dot {
    position: absolute;
    left: 50%;
    top: 50%;
    width: 7px;
    height: 7px;
    margin: -3.5px 0 0 -3.5px;
    border-radius: 9999px;
    background: #22c55e;
    opacity: 0;
    transform: rotate(calc(var(--dot-index) * 45deg)) translateY(-16px);
    animation: page-preloader-fade 0.8s linear infinite;
    animation-delay: calc(var(--dot-index) * 0.1s);
}

@keyframes page-preloader-fade {
    0% {
        opacity: 1;
    }

    100% {
        opacity: 0.15;
    }
}
</style>
