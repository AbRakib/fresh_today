<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import OrderForm from './OrderForm.vue';
import type {
    CustomerOption,
    DeliveryChargeOption,
    ProductOption,
} from './OrderForm.vue';

defineProps<{
    customers: CustomerOption[];
    products: ProductOption[];
    deliveryCharges: DeliveryChargeOption[];
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
        <OrderForm
            :customers="customers"
            :products="products"
            :delivery-charges="deliveryCharges"
            :next-order-number="nextOrderNumber"
        />
    </div>
</template>
