<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Boxes,
    Download,
    Package,
    PackageCheck,
    Search,
    ShoppingCart,
    Wallet,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { Input } from '@/components/ui/input';
import { useCurrency } from '@/composables/useCurrency';

type StockProduct = {
    id: number;
    name: string;
    sku: string | null;
    thumbnail_url: string | null;
    category_name: string | null;
    subcategory_name: string | null;
    unit: string | null;
    purchased_qty: number;
    sold_qty: number;
    available_qty: number;
    stock_quantity: number;
    stock_value: number;
};

type Summary = {
    total_products: number;
    total_purchased_qty: number;
    total_sold_qty: number;
    total_stock_qty: number;
    total_stock_value: number;
};

const props = defineProps<{
    filters: { search: string };
    products: StockProduct[];
    summary: Summary;
}>();

const search = ref(props.filters.search || '');
const { money } = useCurrency();

let searchTimer: ReturnType<typeof setTimeout> | undefined;

const applySearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(
            '/reports/stock',
            { search: search.value || undefined },
            { preserveState: true, replace: true },
        );
    }, 250);
};

const downloadPdf = () => {
    const params = new URLSearchParams();

    if (search.value) {
        params.set('search', search.value);
    }

    window.location.href = `/reports/stock/pdf${params.toString() ? `?${params.toString()}` : ''}`;
};

const varianceLabel = (product: StockProduct) =>
    product.stock_quantity - product.available_qty;

const filteredProducts = computed(() => props.products);

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Stock Report', href: '/reports/stock' }],
    },
});
</script>

<template>
    <Head title="Stock Report" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-md border p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm text-muted-foreground">
                            Products
                        </div>
                        <div class="mt-1 text-2xl font-semibold tabular-nums">
                            {{ summary.total_products }}
                        </div>
                    </div>
                    <div
                        class="flex size-10 items-center justify-center rounded-md bg-muted text-muted-foreground"
                    >
                        <Boxes class="size-5" />
                    </div>
                </div>
            </div>
            <div class="rounded-md border p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm text-muted-foreground">
                            Purchased Qty
                        </div>
                        <div class="mt-1 text-2xl font-semibold tabular-nums">
                            {{ summary.total_purchased_qty }}
                        </div>
                    </div>
                    <div
                        class="flex size-10 items-center justify-center rounded-md bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                    >
                        <PackageCheck class="size-5" />
                    </div>
                </div>
            </div>
            <div class="rounded-md border p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm text-muted-foreground">
                            Sold Qty
                        </div>
                        <div class="mt-1 text-2xl font-semibold tabular-nums">
                            {{ summary.total_sold_qty }}
                        </div>
                    </div>
                    <div
                        class="flex size-10 items-center justify-center rounded-md bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300"
                    >
                        <ShoppingCart class="size-5" />
                    </div>
                </div>
            </div>
            <div class="rounded-md border p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm text-muted-foreground">
                            Stock Value
                        </div>
                        <div class="mt-1 text-2xl font-semibold tabular-nums">
                            {{ money(summary.total_stock_value) }}
                        </div>
                    </div>
                    <div
                        class="flex size-10 items-center justify-center rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                    >
                        <Wallet class="size-5" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-2">
            <div
                class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="relative max-w-sm">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="search"
                        class="pl-9"
                        placeholder="Search stock"
                        aria-label="Search stock"
                        @input="applySearch"
                    />
                </div>
                <button
                    type="button"
                    class="inline-flex h-8 items-center justify-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    @click="downloadPdf"
                >
                    <Download class="size-4" />
                    Download PDF
                </button>
            </div>

            <div class="overflow-hidden rounded-md border">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[980px] table-fixed text-sm">
                        <colgroup>
                            <col class="w-[6%]" />
                            <col class="w-[24%]" />
                            <col class="w-[18%]" />
                            <col class="w-[12%]" />
                            <col class="w-[12%]" />
                            <col class="w-[12%]" />
                            <col class="w-[16%]" />
                        </colgroup>
                        <thead class="border-b bg-muted/50 text-left">
                            <tr>
                                <th class="px-4 py-3 font-medium">SL</th>
                                <th class="px-4 py-3 font-medium">Product</th>
                                <th class="px-4 py-3 text-center font-medium">
                                    Category
                                </th>
                                <th class="px-4 py-3 text-center font-medium">
                                    Purchased
                                </th>
                                <th class="px-4 py-3 text-center font-medium">
                                    Sold
                                </th>
                                <th class="px-4 py-3 text-center font-medium">
                                    Stock
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Stock Value
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="(product, index) in filteredProducts"
                                :key="product.id"
                                class="hover:bg-muted/30"
                            >
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ index + 1 }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img
                                            v-if="product.thumbnail_url"
                                            :src="product.thumbnail_url"
                                            :alt="product.name"
                                            class="size-10 shrink-0 rounded-md border object-cover"
                                        />
                                        <div
                                            v-else
                                            class="flex size-10 shrink-0 items-center justify-center rounded-md border bg-muted text-muted-foreground"
                                        >
                                            <Package class="size-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="truncate font-medium">
                                                {{ product.name }}
                                            </div>
                                            <div
                                                class="truncate text-xs text-muted-foreground"
                                            >
                                                {{ product.sku || 'No SKU' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="truncate">
                                        {{
                                            product.category_name ||
                                            'No category'
                                        }}
                                    </div>
                                    <div
                                        v-if="product.subcategory_name"
                                        class="truncate text-xs text-muted-foreground"
                                    >
                                        {{ product.subcategory_name }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center tabular-nums">
                                    {{ product.purchased_qty }}
                                    {{ product.unit || 'pcs' }}
                                </td>
                                <td class="px-4 py-3 text-center tabular-nums">
                                    {{ product.sold_qty }}
                                    {{ product.unit || 'pcs' }}
                                </td>
                                <td class="px-4 py-3 text-center tabular-nums">
                                    <div class="font-medium">
                                        {{ product.stock_quantity }}
                                        {{ product.unit || 'pcs' }}
                                    </div>
                                    <div
                                        v-if="varianceLabel(product) !== 0"
                                        class="text-xs text-amber-600 dark:text-amber-400"
                                    >
                                        Batch variance:
                                        {{ varianceLabel(product) }}
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-medium tabular-nums"
                                >
                                    {{ money(product.stock_value) }}
                                </td>
                            </tr>
                            <tr v-if="filteredProducts.length === 0">
                                <td
                                    colspan="7"
                                    class="px-4 py-10 text-center text-muted-foreground"
                                >
                                    No stock records found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
