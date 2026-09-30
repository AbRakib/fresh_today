<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import SliderForm from './SliderForm.vue';
import type { ProductOption } from './SliderForm.vue';

defineProps<{
    products: ProductOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Sliders', href: '/sliders' },
            { title: 'Add slider', href: '/sliders/create' },
        ],
    },
});
</script>

<template>
    <Head title="Add slider" />
    <div class="mx-auto w-full max-w-5xl space-y-6 p-4 md:p-6">
        <Heading
            title="Add slider"
            description="Create a website slider with a cropped banner image."
        />
        <SliderForm :products="products" />
    </div>
</template>
