<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import SliderForm from './SliderForm.vue';
import type { ProductOption, SliderFormData } from './SliderForm.vue';

const props = defineProps<{
    products: ProductOption[];
    slider: SliderFormData;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Sliders', href: '/sliders' },
            { title: 'Edit slider', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="`Edit slider #${props.slider.id}`" />
    <div class="mx-auto w-full max-w-5xl space-y-6 p-4 md:p-6">
        <Heading
            title="Edit slider"
            :description="`Slider #${props.slider.id}`"
        />
        <SliderForm :products="products" :slider="slider" />
    </div>
</template>
