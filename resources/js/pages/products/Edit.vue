<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import ProductForm from './ProductForm.vue';
import type {
    Category,
    ProductFormData,
    Subcategory,
    Unit,
} from './ProductForm.vue';

const props = defineProps<{
    categories: Category[];
    subcategories: Subcategory[];
    units: Unit[];
    product: ProductFormData;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Products', href: '/products' },
            { title: 'Edit product', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="`Edit ${props.product.name}`" />
    <div class="mx-auto w-full max-w-5xl space-y-6 p-4 md:p-6">
        <Heading title="Edit product" :description="props.product.slug" />
        <ProductForm
            :categories="categories"
            :subcategories="subcategories"
            :units="units"
            :product="product"
        />
    </div>
</template>
