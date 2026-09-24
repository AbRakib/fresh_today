<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import PurchaseForm from './PurchaseForm.vue';
import type { ProductOption, SupplierOption } from './PurchaseForm.vue';

defineProps<{
    suppliers: SupplierOption[];
    products: ProductOption[];
    nextPurchaseNumber: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Purchases', href: '/purchases' },
            { title: 'Create purchase', href: '/purchases/create' },
        ],
    },
});
</script>

<template>
    <Head title="Create purchase" />
    <div class="mx-auto w-full max-w-6xl space-y-6 p-4 md:p-6">
        <Heading
            title="Create purchase"
            description="Record supplier purchases and product pricing."
        />
        <PurchaseForm
            :suppliers="suppliers"
            :products="products"
            :next-purchase-number="nextPurchaseNumber"
        />
    </div>
</template>
