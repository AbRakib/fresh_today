<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import ProductForm from './ProductForm.vue';
import type { Category, Subcategory, Unit } from './ProductForm.vue';

defineProps<{
    categories: Category[];
    subcategories: Subcategory[];
    units: Unit[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Products', href: '/products' },
            { title: 'Add product', href: '/products/create' },
        ],
    },
});
</script>

<template>
    <Head title="Add product" />
    <div class="mx-auto w-full max-w-5xl space-y-6 p-4 md:p-6">
        <Heading
            title="Add product"
            description="Create a catalog item with pricing and stock details."
        />
        <ProductForm
            :categories="categories"
            :subcategories="subcategories"
            :units="units"
            :show-product-state-fields="false"
        />
    </div>
</template>
