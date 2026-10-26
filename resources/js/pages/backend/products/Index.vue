<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    MoreVertical,
    Package,
    Pencil,
    Plus,
    Search,
    Star,
    Trash2,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { useCurrency } from '@/composables/useCurrency';
import { formatDate } from '@/lib/utils';

type Product = {
    id: number;
    category_id: number;
    category_name: string | null;
    subcategory_id: number | null;
    subcategory_name: string | null;
    name: string;
    slug: string;
    sku: string | null;
    thumbnail_url: string | null;
    short_description: string | null;
    description: string | null;
    unit: string | null;
    gross_weight: string | null;
    weight: string | null;
    cost_price: string;
    sale_price: string | null;
    discount_percentage: string | null;
    badge: string | null;
    stock_quantity: number;
    minimum_order_quantity: number;
    is_featured: number;
    status: number;
    created_at: string | null;
};

const { products } = defineProps<{ products: Product[] }>();

const search = ref('');
const detailsOpen = ref(false);
const detailsProduct = ref<Product | null>(null);
const deleteOpen = ref(false);
const selectedProduct = ref<Product | null>(null);
const deleting = ref(false);
const { money: formatMoney } = useCurrency();

const filteredProducts = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return products;
    }

    return products.filter((product) =>
        [
            product.name,
            product.sku,
            product.category_name,
            product.subcategory_name,
            product.badge,
        ]
            .filter(Boolean)
            .some((value) => value!.toLowerCase().includes(query)),
    );
});

const money = (value: string | null) => {
    if (value === null || value === '') {
        return 'N/A';
    }

    return formatMoney(value);
};

const openDetails = (product: Product) => {
    detailsProduct.value = product;
    detailsOpen.value = true;
};

const productDetails = computed(() => {
    const product = detailsProduct.value;

    if (!product) {
        return [];
    }

    return [
        ['Category', product.category_name || 'N/A'],
        ['Subcategory', product.subcategory_name || 'N/A'],
        ['Unit', product.unit || 'N/A'],
        ['Minimum order', product.minimum_order_quantity],
        ['Net weight', product.weight || 'N/A'],
        ['Gross weight', product.gross_weight || 'N/A'],
        ['Badge', product.badge || 'N/A'],
        ['Created', formatDate(product.created_at) || 'N/A'],
    ];
});

const openDelete = (product: Product) => {
    selectedProduct.value = product;
    deleteOpen.value = true;
};

const deleteProduct = () => {
    if (!selectedProduct.value) {
        return;
    }

    deleting.value = true;
    router.delete(`/products/${selectedProduct.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteOpen.value = false;
            selectedProduct.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
};

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Products', href: '/products' }],
    },
});
</script>

<template>
    <Head title="Products" />

    <div class="flex h-full flex-1 flex-col gap-2 p-4 md:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="relative max-w-sm">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    class="pl-9"
                    placeholder="Search products"
                    aria-label="Search products"
                />
            </div>
            <Button class="shrink-0" @click="router.visit('/products/create')">
                <Plus class="size-4" />
                Add product
            </Button>
        </div>

        <div class="overflow-hidden rounded-md border">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="w-16 px-4 py-3 font-medium">SL</th>
                            <th class="px-4 py-3 font-medium">Product</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Price</th>
                            <th class="px-4 py-3 font-medium">Stock</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Created</th>
                            <th class="w-24 px-4 py-3 text-right font-medium">
                                Actions
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
                                    <button
                                        type="button"
                                        class="shrink-0 cursor-pointer rounded-md focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                        :aria-label="`View details for ${product.name}`"
                                        @click="openDetails(product)"
                                    >
                                        <img
                                            v-if="product.thumbnail_url"
                                            :src="product.thumbnail_url"
                                            :alt="product.name"
                                            class="size-11 rounded-md border object-cover"
                                        />
                                        <span
                                            v-else
                                            class="flex size-11 items-center justify-center rounded-md border bg-muted text-muted-foreground"
                                        >
                                            <Package class="size-4" />
                                        </span>
                                    </button>
                                    <div class="min-w-0">
                                        <div
                                            class="flex items-center gap-2 font-medium"
                                        >
                                            <button
                                                type="button"
                                                class="cursor-pointer truncate rounded-sm text-left hover:underline focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                                @click="openDetails(product)"
                                            >
                                                {{ product.name }}
                                            </button>
                                            <Star
                                                v-if="product.is_featured"
                                                class="size-3.5 fill-amber-400 text-amber-500"
                                            />
                                        </div>
                                        <div
                                            class="truncate text-muted-foreground"
                                        >
                                            {{ product.sku || product.slug }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                <div>{{ product.category_name }}</div>
                                <div
                                    v-if="product.subcategory_name"
                                    class="text-xs"
                                >
                                    {{ product.subcategory_name }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">
                                    {{ money(product.sale_price) }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ product.stock_quantity }}
                                {{ product.unit || 'pcs' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        product.status
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    {{ product.status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{
                                    formatDate(product.created_at) ||
                                    'Not available'
                                }}
                            </td>
                            <td class="px-4 py-3">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="ml-auto flex"
                                            title="Product actions"
                                        >
                                            <MoreVertical class="size-4" />
                                            <span class="sr-only"
                                                >Product actions</span
                                            >
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            @click="
                                                router.visit(
                                                    `/products/${product.id}/edit`,
                                                )
                                            "
                                        >
                                            <Pencil class="size-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            variant="destructive"
                                            @click="openDelete(product)"
                                        >
                                            <Trash2 class="size-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="filteredProducts.length === 0">
                            <td
                                colspan="8"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <Package class="mx-auto mb-3 size-8" />
                                No products found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <Dialog v-model:open="detailsOpen">
        <DialogContent
            class="flex max-h-[calc(100dvh-2rem)] flex-col gap-0 overflow-hidden p-0 sm:max-w-4xl"
        >
            <DialogHeader class="border-b px-6 py-5 pr-12">
                <DialogDescription
                    class="text-xs font-semibold tracking-widest text-primary uppercase"
                    >Inventory / Product details</DialogDescription
                >
                <DialogTitle class="text-lg">Product overview</DialogTitle>
            </DialogHeader>

            <div v-if="detailsProduct" class="min-h-0 overflow-y-auto">
                <div
                    class="grid gap-6 p-5 sm:p-6 md:grid-cols-[0.9fr_1.1fr] md:gap-8"
                >
                    <div class="min-w-0">
                        <div
                            class="relative flex aspect-square items-center justify-center overflow-hidden rounded-xl border bg-muted/30 p-4"
                        >
                            <img
                                v-if="detailsProduct.thumbnail_url"
                                :src="detailsProduct.thumbnail_url"
                                :alt="detailsProduct.name"
                                class="h-full w-full rounded-lg object-contain"
                            />
                            <div
                                v-else
                                class="flex flex-col items-center gap-3 text-muted-foreground"
                            >
                                <Package class="size-16 stroke-1" />
                                <span class="text-sm">No product image</span>
                            </div>
                            <span
                                v-if="detailsProduct.is_featured"
                                class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 shadow-sm"
                            >
                                <Star class="size-3 fill-current" /> Featured
                            </span>
                        </div>
                        <div
                            class="mt-3 flex items-center justify-between gap-3 rounded-lg bg-muted/50 px-4 py-3 text-xs"
                        >
                            <span class="text-muted-foreground"
                                >Product SKU</span
                            >
                            <span class="font-mono font-semibold break-all">{{
                                detailsProduct.sku || 'N/A'
                            }}</span>
                        </div>
                    </div>

                    <div class="min-w-0 space-y-5">
                        <div>
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="
                                        detailsProduct.status
                                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    <span
                                        class="size-1.5 rounded-full bg-current"
                                    />
                                    {{
                                        detailsProduct.status
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </span>
                                <span
                                    class="rounded-full border px-2.5 py-1 text-xs text-muted-foreground"
                                    >{{
                                        detailsProduct.category_name ||
                                        'Uncategorized'
                                    }}</span
                                >
                            </div>
                            <h2
                                class="text-2xl leading-tight font-semibold tracking-tight break-words"
                            >
                                {{ detailsProduct.name }}
                            </h2>
                            <p
                                v-if="detailsProduct.short_description"
                                class="mt-3 text-sm leading-relaxed whitespace-pre-line text-muted-foreground"
                            >
                                {{ detailsProduct.short_description }}
                            </p>
                        </div>

                        <div class="rounded-xl border bg-muted/20 p-4">
                            <div
                                class="flex flex-wrap items-center justify-between gap-3"
                            >
                                <div>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Sale price
                                    </p>
                                    <p
                                        class="mt-1 text-3xl font-semibold tracking-tight"
                                    >
                                        {{ money(detailsProduct.sale_price) }}
                                    </p>
                                </div>
                                <span
                                    v-if="
                                        Number(
                                            detailsProduct.discount_percentage,
                                        ) > 0
                                    "
                                    class="rounded-md bg-primary/10 px-2.5 py-1.5 text-xs font-semibold text-primary"
                                    >{{
                                        Number(
                                            detailsProduct.discount_percentage,
                                        )
                                    }}% discount</span
                                >
                            </div>
                            <div
                                class="mt-4 flex flex-wrap justify-between gap-2 border-t pt-3 text-xs text-muted-foreground"
                            >
                                <span
                                    >Cost price
                                    <strong
                                        class="ml-1 font-medium text-foreground"
                                        >{{
                                            money(detailsProduct.cost_price)
                                        }}</strong
                                    ></span
                                >
                                <span
                                    v-if="
                                        !Number(
                                            detailsProduct.discount_percentage,
                                        )
                                    "
                                    >No discount</span
                                >
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-3 rounded-xl border p-4"
                        >
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-lg"
                                :class="
                                    detailsProduct.stock_quantity > 0
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                        : 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300'
                                "
                            >
                                <Package class="size-5" />
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-muted-foreground">
                                    Available stock
                                </p>
                                <p class="mt-0.5 text-lg font-semibold">
                                    {{ detailsProduct.stock_quantity }}
                                    <span
                                        class="text-sm font-normal text-muted-foreground"
                                        >{{
                                            detailsProduct.unit || 'pcs'
                                        }}</span
                                    >
                                </p>
                            </div>
                            <span
                                class="text-xs font-medium"
                                :class="
                                    detailsProduct.stock_quantity > 0
                                        ? 'text-emerald-700 dark:text-emerald-400'
                                        : 'text-rose-700 dark:text-rose-400'
                                "
                                >{{
                                    detailsProduct.stock_quantity > 0
                                        ? 'In stock'
                                        : 'Out of stock'
                                }}</span
                            >
                        </div>
                    </div>
                </div>

                <div class="space-y-6 border-t bg-muted/10 p-5 sm:p-6">
                    <section>
                        <h3 class="mb-4 text-sm font-semibold">
                            Product information
                        </h3>
                        <dl
                            class="grid grid-cols-2 gap-x-6 gap-y-4 lg:grid-cols-4"
                        >
                            <div
                                v-for="[label, value] in productDetails"
                                :key="label"
                                class="min-w-0"
                            >
                                <dt class="text-xs text-muted-foreground">
                                    {{ label }}
                                </dt>
                                <dd
                                    class="mt-1 text-sm font-medium break-words"
                                >
                                    {{ value }}
                                </dd>
                            </div>
                        </dl>
                    </section>
                    <section
                        v-if="detailsProduct.description"
                        class="border-t pt-5"
                    >
                        <h3 class="mb-3 text-sm font-semibold">
                            About this product
                        </h3>
                        <div
                            class="text-sm leading-relaxed break-words text-muted-foreground [&_a]:text-primary [&_a]:underline [&_h2]:my-3 [&_h2]:text-lg [&_h2]:font-semibold [&_h3]:my-3 [&_h3]:font-semibold [&_li]:my-1 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-3 [&_strong]:font-semibold [&_strong]:text-foreground [&_ul]:list-disc [&_ul]:pl-5"
                            v-html="detailsProduct.description"
                        />
                    </section>
                </div>
            </div>

            <DialogFooter
                class="shrink-0 flex-row items-center justify-end border-t bg-background px-6 py-4"
            >
                <Button variant="outline" @click="detailsOpen = false"
                    >Close</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="deleteOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete product</DialogTitle>
                <DialogDescription>
                    Delete {{ selectedProduct?.name }}? This product will no
                    longer appear in the product list.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="deleteOpen = false"
                    >Cancel</Button
                >
                <Button
                    variant="destructive"
                    :disabled="deleting"
                    @click="deleteProduct"
                    >Delete</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
