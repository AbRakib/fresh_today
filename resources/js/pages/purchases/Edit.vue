<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import PurchaseForm from './PurchaseForm.vue';
import type {
    ProductOption,
    PurchaseFormData,
    SupplierOption,
} from './PurchaseForm.vue';

defineProps<{
    suppliers: SupplierOption[];
    products: ProductOption[];
    purchase: PurchaseFormData;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Purchases', href: '/purchases' },
            { title: 'Edit purchase', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Edit purchase" />
    <div class="mx-auto w-full max-w-6xl space-y-6 p-4 md:p-6">
        <Heading
            title="Edit purchase"
            description="Update supplier, item quantity, expiry, and pricing."
        />
        <PurchaseForm
            :suppliers="suppliers"
            :products="products"
            :purchase="purchase"
        />
    </div>
</template>
