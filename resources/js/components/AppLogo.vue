<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const page = usePage();
const settings = computed(() => page.props.settings);
const name = computed(
    () => settings.value?.company_name || page.props.name || 'Fresh Deal',
);
const email = computed(() => settings.value?.email);
const logoUrl = computed(() => settings.value?.logo_url);
</script>

<template>
    <div
        class="flex aspect-square size-8 shrink-0 items-center justify-center overflow-hidden rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
    >
        <img
            v-if="logoUrl"
            :src="logoUrl"
            :alt="`${name} logo`"
            class="size-full object-contain"
        />
        <AppLogoIcon
            v-else
            class="size-5 fill-current text-white dark:text-black"
        />
    </div>
    <div class="ml-1 grid min-w-0 flex-1 text-left">
        <span class="truncate text-sm leading-tight font-semibold">
            {{ name }}
        </span>
        <span
            v-if="email"
            class="truncate text-xs leading-tight text-sidebar-foreground/70"
        >
            {{ email }}
        </span>
    </div>
</template>
