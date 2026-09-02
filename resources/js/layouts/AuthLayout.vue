<script setup lang="ts">
import AuthCardLayout from '@/layouts/auth/AuthCardLayout.vue';
import AuthSimpleLayout from '@/layouts/auth/AuthSimpleLayout.vue';

const {
    title = '',
    description = '',
    variant = 'simple',
} = defineProps<{
    title?: string;
    description?: string;
    variant?: 'simple' | 'card';
}>();
</script>

<template>
    <component
        :is="variant === 'card' ? AuthCardLayout : AuthSimpleLayout"
        :title="title"
        :description="description"
    >
        <slot />
    </component>
</template>
