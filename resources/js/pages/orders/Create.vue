<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import OrderForm from './OrderForm.vue';
import type { CustomerOption, ProductOption } from './OrderForm.vue';

defineProps<{
    customers: CustomerOption[];
    products: ProductOption[];
    nextOrderNumber: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Orders', href: '/orders' },
            { title: 'Create order', href: '/orders/create' },
        ],
    },
});
</script>

<template>
    <Head title="Create order" />
    <div class="mx-auto w-full max-w-6xl space-y-6 p-4 md:p-6">
        <Heading
            title="Create order"
            description="Create a customer order and reserve product stock."
        />
        <OrderForm
            :customers="customers"
            :products="products"
            :next-order-number="nextOrderNumber"
        />
    </div>
</template>
