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
import Heading from '@/components/Heading.vue';
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
                                    <img
                                        v-if="product.thumbnail_url"
                                        :src="product.thumbnail_url"
                                        :alt="product.name"
                                        class="size-11 rounded-md border object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex size-11 items-center justify-center rounded-md border bg-muted text-muted-foreground"
                                    >
                                        <Package class="size-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div
                                            class="flex items-center gap-2 font-medium"
                                        >
                                            <span class="truncate">{{
                                                product.name
                                            }}</span>
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
