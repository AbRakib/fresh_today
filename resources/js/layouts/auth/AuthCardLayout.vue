<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage();
const settingsLogoUrl = page.props.settings?.logo_url;
const appName = page.props.settings?.company_name || page.props.name;
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10"
    >
        <div class="flex w-full max-w-md flex-col gap-6">
            <div class="flex flex-col gap-6">
                <Card class="rounded-xl">
                    <CardHeader class="px-10 pt-8 pb-0 text-center">
                        <Link
                            :href="home()"
                            class="mb-2 flex h-16 w-full items-center justify-center"
                        >
                            <img
                                v-if="settingsLogoUrl"
                                :src="settingsLogoUrl"
                                :alt="`${appName} logo`"
                                class="max-h-16 max-w-full object-contain"
                            />
                            <AppLogoIcon
                                v-else
                                class="size-12 fill-current text-black dark:text-white"
                            />
                        </Link>
                        <CardTitle class="text-xl">{{ title }}</CardTitle>
                        <CardDescription>
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-10 py-8">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
