<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import {
    Check,
    Package,
    Plus,
    Search,
    Trash2,
    UserRound,
    UserRoundPlus,
} from '@lucide/vue';
import { computed, nextTick, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export type SupplierOption = {
    id: number;
    name: string;
    photo_url: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
    note: string | null;
};

export type ProductOption = {
    id: number;
    name: string;
    sku: string | null;
    thumbnail_url: string | null;
    unit: string | null;
    regular_price: string;
    sale_price: string | null;
    stock_quantity: number;
};

export type PurchaseItemFormData = {
    product_id: number | string;
    purchase_qty: number | string;
    expire_date: string;
    purchase_price: number | string;
    sell_price: number | string;
};

export type PurchaseFormData = {
    id: number;
    purchase_number: string;
    supplier_id: number;
    purchase_date: string;
    note: string | null;
    items: PurchaseItemFormData[];
};

const props = defineProps<{
    suppliers: SupplierOption[];
    products: ProductOption[];
    nextPurchaseNumber?: string;
    purchase?: PurchaseFormData;
}>();

const today = new Date().toISOString().slice(0, 10);
const supplierSearch = ref('');
const supplierPickerOpen = ref(false);
const productSearch = ref('');
const productPickerOpen = ref(false);
const submitAttempted = ref(false);

const itemFromProduct = (product: ProductOption): PurchaseItemFormData => ({
    product_id: product.id,
    purchase_qty: 1,
    expire_date: '',
    purchase_price: product.regular_price || 0,
    sell_price: product.sale_price || product.regular_price || 0,
});

const form = useForm({
    supplier_id: props.purchase?.supplier_id ?? '',
    purchase_date: props.purchase?.purchase_date ?? today,
    note: props.purchase?.note ?? '',
    items: props.purchase?.items ?? [],
});

const filteredSuppliers = computed(() => {
    const query = supplierSearch.value.trim().toLowerCase();

    if (!query) return props.suppliers;

    return props.suppliers.filter((supplier) =>
        [supplier.name, supplier.email, supplier.phone, supplier.address]
            .filter(Boolean)
            .some((value) => value!.toLowerCase().includes(query)),
    );
});

const selectedSupplier = computed(() =>
    props.suppliers.find(
        (supplier) => String(supplier.id) === String(form.supplier_id),
    ),
);

const chooseSupplier = (supplierId: number) => {
    form.supplier_id = supplierId;
    supplierPickerOpen.value = false;
    supplierSearch.value = '';
};

const filteredProducts = computed(() => {
    const query = productSearch.value.trim().toLowerCase();

    if (!query) return props.products;

    return props.products.filter((product) =>
        [product.name, product.sku, product.unit]
            .filter(Boolean)
            .some((value) => value!.toLowerCase().includes(query)),
    );
});

const purchaseNumber = computed(
    () => props.purchase?.purchase_number ?? props.nextPurchaseNumber ?? 'Auto',
);

const subtotal = computed(() =>
    form.items.reduce(
        (sum, item) =>
            sum +
            Number(item.purchase_qty || 0) * Number(item.purchase_price || 0),
        0,
    ),
);

const money = (value: number | string) =>
    Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

const errorFor = (key: string) => form.errors[key as keyof typeof form.errors];

const addItem = () => {
    productSearch.value = '';
    productPickerOpen.value = true;
};

const removeItem = (index: number) => {
    form.items.splice(index, 1);
};

const selectedProduct = (productId: number | string) =>
    props.products.find((product) => String(product.id) === String(productId));

const productIsAdded = (productId: number) =>
    form.items.some((item) => String(item.product_id) === String(productId));

const chooseProduct = (product: ProductOption) => {
    if (productIsAdded(product.id)) return;

    form.items.push(itemFromProduct(product));
    productPickerOpen.value = false;
    productSearch.value = '';
};

const handleError = async (errors: Record<string, unknown>) => {
    submitAttempted.value = true;
    await nextTick();

    const firstError = Object.keys(errors)[0];
    if (!firstError) return;

    document
        .querySelector<HTMLElement>(`[name="${firstError}"]`)
        ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const submit = () => {
    submitAttempted.value = true;

    const url = props.purchase
        ? `/purchases/${props.purchase.id}`
        : '/purchases';

    form.post(url, {
        preserveScroll: true,
        onError: handleError,
    });
};
</script>

<template>
    <form
        class="space-y-6 [&_button:not(:disabled)]:cursor-pointer [&_input:focus]:!ring-1 [&_input:focus]:!ring-ring/20 [&_select:focus]:border-ring [&_select:focus]:ring-1 [&_select:focus]:ring-ring/20 [&_select:focus]:outline-none [&_textarea:focus]:border-ring [&_textarea:focus]:ring-1 [&_textarea:focus]:ring-ring/20 [&_textarea:focus]:outline-none"
        :class="{ 'show-required-errors': submitAttempted }"
        novalidate
        @submit.prevent="submit"
    >
        <div class="grid gap-5 rounded-md border p-4 sm:p-5 lg:grid-cols-4">
            <div class="grid gap-1.5 lg:col-span-2">
                <button
                    v-if="!selectedSupplier"
                    type="button"
                    class="flex min-h-36 w-full max-w-52 flex-col items-center justify-center gap-2 rounded-md border bg-background p-5 text-center transition-colors hover:border-primary/50 hover:bg-muted/20 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                    @click="supplierPickerOpen = true"
                >
                    <UserRoundPlus
                        class="size-9 text-muted-foreground/70"
                        aria-hidden="true"
                    />
                    <span class="font-medium text-primary">Add a supplier</span>
                </button>
                <div
                    v-else
                    class="min-h-36 rounded-md border bg-background p-5 text-sm"
                >
                    <p class="mb-3 font-medium">Purchase from</p>
                    <div class="flex items-start gap-3">
                        <Avatar class="size-14 shrink-0">
                            <AvatarImage
                                v-if="selectedSupplier.photo_url"
                                :src="selectedSupplier.photo_url"
                                :alt="selectedSupplier.name"
                            />
                            <div
                                v-else
                                class="flex size-full items-center justify-center rounded-full bg-muted text-muted-foreground"
                                :aria-label="`${selectedSupplier.name} default photo`"
                            >
                                <UserRound class="size-7" aria-hidden="true" />
                            </div>
                        </Avatar>
                        <div class="min-w-0">
                            <p class="font-semibold">
                                {{ selectedSupplier.name }}
                            </p>
                            <p v-if="selectedSupplier.email" class="mt-0.5">
                                {{ selectedSupplier.email }}
                            </p>
                            <p v-if="selectedSupplier.phone" class="mt-0.5">
                                {{ selectedSupplier.phone }}
                            </p>
                        </div>
                    </div>
                    <p
                        v-if="selectedSupplier.address"
                        class="mt-3 whitespace-pre-line text-muted-foreground"
                    >
                        {{ selectedSupplier.address }}
                    </p>
                    <p
                        v-if="selectedSupplier.note"
                        class="mt-2 text-xs whitespace-pre-line text-muted-foreground"
                    >
                        {{ selectedSupplier.note }}
                    </p>
                    <button
                        type="button"
                        class="mt-3 font-medium text-blue-600 underline-offset-4 hover:text-blue-700 hover:underline focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:outline-none dark:text-blue-400 dark:hover:text-blue-300"
                        @click="supplierPickerOpen = true"
                    >
                        Choose a different supplier
                    </button>
                </div>
                <input
                    name="supplier_id"
                    type="hidden"
                    :value="form.supplier_id"
                />
                <InputError :message="form.errors.supplier_id" />
            </div>

            <div class="hidden lg:block" aria-hidden="true" />

            <div class="grid content-start gap-4">
                <div class="grid gap-1.5">
                    <Label for="purchase_number">Purchase number</Label>
                    <Input
                        id="purchase_number"
                        :model-value="purchaseNumber"
                        readonly
                        class="bg-muted/40"
                    />
                </div>

                <div class="grid gap-1.5">
                    <Label for="purchase_date"
                        >Purchase date
                        <span class="text-destructive" aria-hidden="true"
                            >*</span
                        ></Label
                    >
                    <Input
                        id="purchase_date"
                        v-model="form.purchase_date"
                        type="date"
                        name="purchase_date"
                        required
                    />
                    <InputError :message="form.errors.purchase_date" />
                </div>
            </div>
        </div>

        <div class="space-y-4 rounded-md border p-4 sm:p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-medium">Items</h2>
                    <p class="text-sm text-muted-foreground">
                        Add purchase products with quantity and pricing.
                    </p>
                </div>
                <Button type="button" variant="outline" @click="addItem">
                    <Plus class="size-4" />
                    Add item
                </Button>
            </div>

            <div class="space-y-4">
                <div
                    v-if="form.items.length === 0"
                    class="rounded-md border border-dashed px-4 py-10 text-center text-sm text-muted-foreground"
                >
                    No items added. Use Add item to choose a product.
                </div>

                <div
                    v-for="(item, index) in form.items"
                    :key="index"
                    class="grid gap-4 rounded-md border p-4 xl:grid-cols-[minmax(220px,2fr)_minmax(90px,0.8fr)_minmax(150px,1fr)_minmax(130px,1fr)_minmax(130px,1fr)_2.25rem] xl:items-start"
                >
                    <div class="grid gap-1.5">
                        <Label>Item</Label>
                        <input
                            :name="`items.${index}.product_id`"
                            type="hidden"
                            :value="item.product_id"
                        />
                        <div
                            class="flex h-9 items-center rounded-md border bg-muted/20 px-3 text-sm"
                        >
                            <p class="font-medium">
                                {{ selectedProduct(item.product_id)?.name }}
                            </p>
                        </div>
                        <InputError
                            :message="errorFor(`items.${index}.product_id`)"
                        />
                    </div>

                    <div class="grid gap-1.5">
                        <Label :for="`purchase_qty_${index}`"
                            >Qty
                            <span class="font-normal text-muted-foreground">
                                ({{
                                    selectedProduct(item.product_id)?.unit ||
                                    'pcs'
                                }})
                            </span>
                            <span class="text-destructive" aria-hidden="true"
                                >*</span
                            ></Label
                        >
                        <Input
                            :id="`purchase_qty_${index}`"
                            v-model="item.purchase_qty"
                            type="number"
                            min="1"
                            step="1"
                            :name="`items.${index}.purchase_qty`"
                            required
                        />
                        <InputError
                            :message="errorFor(`items.${index}.purchase_qty`)"
                        />
                    </div>

                    <div class="grid gap-1.5">
                        <Label :for="`expire_date_${index}`">Expire date</Label>
                        <Input
                            :id="`expire_date_${index}`"
                            v-model="item.expire_date"
                            type="date"
                            :name="`items.${index}.expire_date`"
                        />
                        <InputError
                            :message="errorFor(`items.${index}.expire_date`)"
                        />
                    </div>

                    <div class="grid gap-1.5">
                        <Label :for="`purchase_price_${index}`"
                            >Purchase price
                            <span class="text-destructive" aria-hidden="true"
                                >*</span
                            ></Label
                        >
                        <Input
                            :id="`purchase_price_${index}`"
                            v-model="item.purchase_price"
                            type="number"
                            min="0"
                            step="0.01"
                            :name="`items.${index}.purchase_price`"
                            required
                        />
                        <InputError
                            :message="errorFor(`items.${index}.purchase_price`)"
                        />
                    </div>

                    <div class="grid gap-1.5">
                        <Label :for="`sell_price_${index}`"
                            >Sell price
                            <span class="text-destructive" aria-hidden="true"
                                >*</span
                            ></Label
                        >
                        <Input
                            :id="`sell_price_${index}`"
                            v-model="item.sell_price"
                            type="number"
                            min="0"
                            step="0.01"
                            :name="`items.${index}.sell_price`"
                            required
                        />
                        <InputError
                            :message="errorFor(`items.${index}.sell_price`)"
                        />
                    </div>

                    <div class="grid content-start gap-1.5">
                        <span class="hidden h-5 xl:block" aria-hidden="true" />
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="size-9 justify-self-end text-destructive hover:text-destructive"
                            title="Remove item"
                            @click="removeItem(index)"
                        >
                            <Trash2 class="size-4" />
                            <span class="sr-only">Remove item</span>
                        </Button>
                    </div>
                </div>
            </div>

            <InputError :message="form.errors.items" />
        </div>

        <div class="grid gap-5 rounded-md border p-4 sm:p-5 lg:grid-cols-3">
            <div class="grid gap-1.5 lg:col-span-2">
                <Label for="purchase_note">Note</Label>
                <textarea
                    id="purchase_note"
                    v-model="form.note"
                    name="note"
                    rows="4"
                    placeholder="Optional purchase note"
                    class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                />
                <InputError :message="form.errors.note" />
            </div>

            <div
                class="grid content-start gap-2 rounded-md border bg-muted/20 p-4"
            >
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Total items</span>
                    <span class="font-medium">{{ form.items.length }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Subtotal</span>
                    <span class="font-medium">{{ money(subtotal) }}</span>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <Button
                type="button"
                variant="outline"
                @click="router.visit('/purchases')"
            >
                Cancel
            </Button>
            <Button type="submit" :disabled="form.processing">
                {{
                    form.processing
                        ? 'Saving...'
                        : purchase
                          ? 'Save changes'
                          : 'Submit'
                }}
            </Button>
        </div>

        <Dialog v-model:open="supplierPickerOpen">
            <DialogContent class="gap-0 overflow-hidden p-0 sm:max-w-md">
                <DialogHeader class="border-b px-5 py-4 text-left">
                    <DialogTitle>Select supplier</DialogTitle>
                    <DialogDescription>
                        Search and choose a supplier for this purchase.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-3 p-5">
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            aria-hidden="true"
                        />
                        <Input
                            id="supplier_search"
                            v-model="supplierSearch"
                            placeholder="Search supplier"
                            class="pl-9"
                            autofocus
                        />
                    </div>

                    <div class="max-h-72 overflow-y-auto rounded-md border">
                        <button
                            v-for="supplier in filteredSuppliers"
                            :key="supplier.id"
                            type="button"
                            class="flex w-full items-center justify-between gap-3 border-b px-4 py-3 text-left last:border-b-0 hover:bg-muted/50"
                            @click="chooseSupplier(supplier.id)"
                        >
                            <span class="flex min-w-0 items-start gap-3">
                                <Avatar class="size-10 shrink-0">
                                    <AvatarImage
                                        v-if="supplier.photo_url"
                                        :src="supplier.photo_url"
                                        :alt="supplier.name"
                                    />
                                    <span
                                        v-else
                                        class="flex size-full items-center justify-center rounded-full bg-muted text-muted-foreground"
                                        :aria-label="`${supplier.name} default photo`"
                                    >
                                        <UserRound
                                            class="size-5"
                                            aria-hidden="true"
                                        />
                                    </span>
                                </Avatar>
                                <span class="min-w-0">
                                    <span
                                        class="block truncate text-sm font-medium"
                                    >
                                        {{ supplier.name }}
                                    </span>
                                    <span
                                        v-if="supplier.phone"
                                        class="block text-xs text-muted-foreground"
                                    >
                                        {{ supplier.phone }}
                                    </span>
                                    <span
                                        v-if="supplier.email"
                                        class="block truncate text-xs text-muted-foreground"
                                    >
                                        {{ supplier.email }}
                                    </span>
                                    <span
                                        v-if="supplier.address"
                                        class="mt-0.5 line-clamp-2 block text-xs text-muted-foreground"
                                    >
                                        {{ supplier.address }}
                                    </span>
                                </span>
                            </span>
                            <Check
                                v-if="
                                    String(form.supplier_id) ===
                                    String(supplier.id)
                                "
                                class="size-4 shrink-0 text-primary"
                                aria-hidden="true"
                            />
                        </button>

                        <p
                            v-if="filteredSuppliers.length === 0"
                            class="px-4 py-8 text-center text-sm text-muted-foreground"
                        >
                            No suppliers found.
                        </p>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="productPickerOpen">
            <DialogContent class="gap-0 overflow-hidden p-0 sm:max-w-xl">
                <DialogHeader class="border-b px-5 py-4 text-left">
                    <DialogTitle>Select product</DialogTitle>
                    <DialogDescription>
                        Search and choose a product to add to this purchase.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-3 p-5">
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            aria-hidden="true"
                        />
                        <Input
                            id="product_search"
                            v-model="productSearch"
                            placeholder="Search by product name, SKU, or unit"
                            class="pl-9"
                            autofocus
                        />
                    </div>

                    <div class="max-h-96 overflow-y-auto rounded-md border">
                        <button
                            v-for="product in filteredProducts"
                            :key="product.id"
                            type="button"
                            class="flex w-full items-center justify-between gap-4 border-b px-4 py-3 text-left last:border-b-0 hover:bg-muted/50 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="productIsAdded(product.id)"
                            @click="chooseProduct(product)"
                        >
                            <span class="flex min-w-0 items-center gap-3">
                                <img
                                    v-if="product.thumbnail_url"
                                    :src="product.thumbnail_url"
                                    :alt="product.name"
                                    class="size-12 shrink-0 rounded-md border object-cover"
                                />
                                <span
                                    v-else
                                    class="flex size-12 shrink-0 items-center justify-center rounded-md border bg-muted text-muted-foreground"
                                    :aria-label="`${product.name} default image`"
                                >
                                    <Package
                                        class="size-5"
                                        aria-hidden="true"
                                    />
                                </span>
                                <span class="min-w-0">
                                    <span
                                        class="block truncate text-sm font-medium"
                                    >
                                        {{ product.name }}
                                    </span>
                                    <span
                                        class="block text-xs text-muted-foreground"
                                    >
                                        {{
                                            product.sku
                                                ? `SKU: ${product.sku} · `
                                                : ''
                                        }}
                                        Stock: {{ product.stock_quantity }}
                                        {{ product.unit || 'pcs' }}
                                    </span>
                                </span>
                            </span>
                            <span
                                v-if="productIsAdded(product.id)"
                                class="flex shrink-0 items-center gap-1 text-xs text-primary"
                            >
                                <Check class="size-4" aria-hidden="true" />
                                Added
                            </span>
                            <span
                                v-else
                                class="shrink-0 text-sm font-medium text-primary"
                            >
                                Select
                            </span>
                        </button>

                        <p
                            v-if="filteredProducts.length === 0"
                            class="px-4 py-10 text-center text-sm text-muted-foreground"
                        >
                            No products found.
                        </p>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </form>
</template>

<style scoped>
.show-required-errors :deep(input:required:invalid),
.show-required-errors :deep(select:required:invalid),
.show-required-errors :deep(textarea:required:invalid) {
    border-color: var(--destructive) !important;
}

.show-required-errors :deep(input:required:invalid:focus),
.show-required-errors :deep(select:required:invalid:focus),
.show-required-errors :deep(textarea:required:invalid:focus) {
    outline: none;
    box-shadow: 0 0 0 2px
        color-mix(in oklab, var(--destructive) 30%, transparent) !important;
}
</style>
