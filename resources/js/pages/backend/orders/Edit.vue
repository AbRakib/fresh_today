<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import OrderForm from './OrderForm.vue';
import type {
    CustomerOption,
    DeliveryChargeOption,
    OrderFormData,
    ProductOption,
} from './OrderForm.vue';

defineProps<{
    customers: CustomerOption[];
    products: ProductOption[];
    deliveryCharges: DeliveryChargeOption[];
    order: OrderFormData;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Orders', href: '/orders' },
            { title: 'Edit order', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Edit order" />
    <div class="mx-auto w-full max-w-6xl space-y-6 p-4 md:p-6">
        <Heading
            title="Edit order"
            description="Update customer, products, pricing, and delivery details."
        />
        <OrderForm
            :customers="customers"
            :products="products"
            :delivery-charges="deliveryCharges"
            :order="order"
        />
    </div>
</template>
