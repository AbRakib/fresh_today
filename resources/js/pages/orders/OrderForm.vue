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

export type CustomerOption = {
    id: number;
    name: string;
    photo_url: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
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
export type OrderItemFormData = {
    product_id: number | string;
    order_qty: number | string;
    regular_price: number | string;
    sale_price: number | string;
    discount_amount: number | string;
};
export type OrderFormData = {
    id: number;
    order_number: string;
    customer_id: number;
    order_date: string;
    delivery_date: string | null;
    delivery_address: string | null;
    note: string | null;
    discount_amount: number | string;
    delivery_charge: number | string;
    items: OrderItemFormData[];
};

const props = defineProps<{
    customers: CustomerOption[];
    products: ProductOption[];
    nextOrderNumber?: string;
    order?: OrderFormData;
}>();
const today = new Date().toISOString().slice(0, 10);
const customerSearch = ref('');
const customerPickerOpen = ref(false);
const productSearch = ref('');
const productPickerOpen = ref(false);
const submitAttempted = ref(false);

const form = useForm({
    customer_id: props.order?.customer_id ?? '',
    order_date: props.order?.order_date ?? today,
    delivery_date: props.order?.delivery_date ?? '',
    delivery_address: props.order?.delivery_address ?? '',
    note: props.order?.note ?? '',
    discount_amount: props.order?.discount_amount ?? 0,
    delivery_charge: props.order?.delivery_charge ?? 0,
    items: props.order?.items ?? [],
});
const filteredCustomers = computed(() => {
    const q = customerSearch.value.trim().toLowerCase();

    return q
        ? props.customers.filter((c) =>
              [c.name, c.email, c.phone, c.address]
                  .filter(Boolean)
                  .some((v) => v!.toLowerCase().includes(q)),
          )
        : props.customers;
});
const selectedCustomer = computed(() =>
    props.customers.find((c) => String(c.id) === String(form.customer_id)),
);
const filteredProducts = computed(() => {
    const q = productSearch.value.trim().toLowerCase();

    return q
        ? props.products.filter((p) =>
              [p.name, p.sku, p.unit]
                  .filter(Boolean)
                  .some((v) => v!.toLowerCase().includes(q)),
          )
        : props.products;
});
const selectedProduct = (id: number | string) =>
    props.products.find((p) => String(p.id) === String(id));
const productIsAdded = (id: number) =>
    form.items.some((item) => String(item.product_id) === String(id));
const chooseCustomer = (id: number) => {
    form.customer_id = id;
    customerPickerOpen.value = false;
    customerSearch.value = '';
};
const chooseProduct = (product: ProductOption) => {
    if (productIsAdded(product.id) || product.stock_quantity < 1) {
        return;
    }

    form.items.push({
        product_id: product.id,
        order_qty: 1,
        regular_price: product.regular_price || 0,
        sale_price: product.sale_price || product.regular_price || 0,
        discount_amount: 0,
    });
    productPickerOpen.value = false;
    productSearch.value = '';
};
const subtotal = computed(() =>
    form.items.reduce(
        (sum, item) =>
            sum +
            Math.max(
                0,
                Number(item.sale_price || 0) * Number(item.order_qty || 0) -
                    Number(item.discount_amount || 0),
            ),
        0,
    ),
);
const total = computed(() =>
    Math.max(
        0,
        subtotal.value -
            Number(form.discount_amount || 0) +
            Number(form.delivery_charge || 0),
    ),
);
const money = (value: number | string) =>
    Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
const errorFor = (key: string) => form.errors[key as keyof typeof form.errors];
const submit = () =>
    form.post(props.order ? `/orders/${props.order.id}` : '/orders', {
        preserveScroll: true,
        onError: async (errors) => {
            submitAttempted.value = true;
            await nextTick();
            const key = Object.keys(errors)[0];

            if (key) {
                document
                    .querySelector<HTMLElement>(`[name="${key}"]`)
                    ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },
    });
</script>

<template>
    <form
        class="space-y-6 [&_button:not(:disabled)]:cursor-pointer"
        :class="{ 'show-required-errors': submitAttempted }"
        novalidate
        @submit.prevent="submit"
    >
        <div class="grid gap-5 rounded-md border p-4 sm:p-5 lg:grid-cols-4">
            <div class="grid gap-1.5 lg:col-span-2">
                <button
                    v-if="!selectedCustomer"
                    type="button"
                    class="flex min-h-36 w-full max-w-52 flex-col items-center justify-center gap-2 rounded-md border bg-background p-5 text-center transition-colors hover:border-primary/50 hover:bg-muted/20"
                    @click="customerPickerOpen = true"
                >
                    <UserRoundPlus
                        class="size-9 text-muted-foreground/70"
                    /><span class="font-medium text-primary"
                        >Add a customer</span
                    >
                </button>
                <div
                    v-else
                    class="min-h-36 rounded-md border bg-background p-5 text-sm"
                >
                    <p class="mb-3 font-medium">Order for</p>
                    <div class="flex items-start gap-3">
                        <Avatar class="size-14 shrink-0"
                            ><AvatarImage
                                v-if="selectedCustomer.photo_url"
                                :src="selectedCustomer.photo_url"
                                :alt="selectedCustomer.name" />
                            <div
                                v-else
                                class="flex size-full items-center justify-center rounded-full bg-muted text-muted-foreground"
                            >
                                <UserRound class="size-7" /></div
                        ></Avatar>
                        <div class="min-w-0">
                            <p class="font-semibold">
                                {{ selectedCustomer.name }}
                            </p>
                            <p v-if="selectedCustomer.email" class="mt-0.5">
                                {{ selectedCustomer.email }}
                            </p>
                            <p v-if="selectedCustomer.phone" class="mt-0.5">
                                {{ selectedCustomer.phone }}
                            </p>
                        </div>
                    </div>
                    <p
                        v-if="selectedCustomer.address"
                        class="mt-3 whitespace-pre-line text-muted-foreground"
                    >
                        {{ selectedCustomer.address }}
                    </p>
                    <button
                        type="button"
                        class="mt-3 font-medium text-blue-600 hover:underline"
                        @click="customerPickerOpen = true"
                    >
                        Choose a different customer
                    </button>
                </div>
                <input
                    name="customer_id"
                    type="hidden"
                    :value="form.customer_id"
                /><InputError :message="form.errors.customer_id" />
            </div>
            <div class="grid content-start gap-4 sm:grid-cols-2 lg:col-span-2">
                <div class="grid gap-1.5">
                    <Label for="order_number">Order number</Label
                    ><Input
                        id="order_number"
                        :model-value="
                            order?.order_number ?? nextOrderNumber ?? 'Auto'
                        "
                        readonly
                        class="bg-muted/40"
                    />
                </div>
                <div class="grid gap-1.5">
                    <Label for="order_date"
                        >Order date
                        <span class="text-destructive">*</span></Label
                    ><Input
                        id="order_date"
                        v-model="form.order_date"
                        type="date"
                        name="order_date"
                        required
                    /><InputError :message="form.errors.order_date" />
                </div>
                <div class="grid gap-1.5">
                    <Label for="delivery_date">Delivery date</Label
                    ><Input
                        id="delivery_date"
                        v-model="form.delivery_date"
                        type="date"
                        name="delivery_date"
                        :min="form.order_date"
                    /><InputError :message="form.errors.delivery_date" />
                </div>
                <div class="grid gap-1.5">
                    <Label for="delivery_address">Delivery address</Label
                    ><Input
                        id="delivery_address"
                        v-model="form.delivery_address"
                        name="delivery_address"
                        placeholder="Optional delivery address"
                    /><InputError :message="form.errors.delivery_address" />
                </div>
            </div>
        </div>

        <div class="space-y-4 rounded-md border p-4 sm:p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-medium">Items</h2>
                    <p class="text-sm text-muted-foreground">
                        Add products with quantity and sale pricing.
                    </p>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    @click="productPickerOpen = true"
                    ><Plus class="size-4" />Add item</Button
                >
            </div>
            <div
                v-if="!form.items.length"
                class="rounded-md border border-dashed px-4 py-10 text-center text-sm text-muted-foreground"
            >
                No items added. Use Add item to choose a product.
            </div>
            <div
                v-for="(item, index) in form.items"
                :key="String(item.product_id)"
                class="grid gap-4 rounded-md border p-4 xl:grid-cols-[minmax(220px,2fr)_100px_130px_130px_130px_110px_2.25rem] xl:items-start"
            >
                <div class="grid gap-1.5">
                    <Label>Item</Label
                    ><input
                        :name="`items.${index}.product_id`"
                        type="hidden"
                        :value="item.product_id"
                    />
                    <div
                        class="flex min-h-9 items-center rounded-md border bg-muted/20 px-3 text-sm"
                    >
                        <div>
                            <p class="font-medium">
                                {{ selectedProduct(item.product_id)?.name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Stock:
                                {{
                                    selectedProduct(item.product_id)
                                        ?.stock_quantity
                                }}
                            </p>
                        </div>
                    </div>
                    <InputError
                        :message="errorFor(`items.${index}.product_id`)"
                    />
                </div>
                <div class="grid gap-1.5">
                    <Label :for="`qty_${index}`">Qty</Label
                    ><Input
                        :id="`qty_${index}`"
                        v-model="item.order_qty"
                        type="number"
                        min="1"
                        :max="selectedProduct(item.product_id)?.stock_quantity"
                        :name="`items.${index}.order_qty`"
                        required
                    /><InputError
                        :message="errorFor(`items.${index}.order_qty`)"
                    />
                </div>
                <div class="grid gap-1.5">
                    <Label :for="`regular_${index}`">Regular price</Label
                    ><Input
                        :id="`regular_${index}`"
                        v-model="item.regular_price"
                        type="number"
                        min="0"
                        step="0.01"
                        :name="`items.${index}.regular_price`"
                        required
                    /><InputError
                        :message="errorFor(`items.${index}.regular_price`)"
                    />
                </div>
                <div class="grid gap-1.5">
                    <Label :for="`sale_${index}`">Sale price</Label
                    ><Input
                        :id="`sale_${index}`"
                        v-model="item.sale_price"
                        type="number"
                        min="0"
                        step="0.01"
                        :name="`items.${index}.sale_price`"
                        required
                    /><InputError
                        :message="errorFor(`items.${index}.sale_price`)"
                    />
                </div>
                <div class="grid gap-1.5">
                    <Label :for="`item_discount_${index}`">Discount</Label
                    ><Input
                        :id="`item_discount_${index}`"
                        v-model="item.discount_amount"
                        type="number"
                        min="0"
                        step="0.01"
                        :name="`items.${index}.discount_amount`"
                        required
                    /><InputError
                        :message="errorFor(`items.${index}.discount_amount`)"
                    />
                </div>
                <div class="grid gap-1.5">
                    <Label>Line total</Label>
                    <div
                        class="flex h-9 items-center justify-end rounded-md border bg-muted/20 px-3 text-sm font-medium"
                    >
                        {{
                            money(
                                Math.max(
                                    0,
                                    Number(item.sale_price || 0) *
                                        Number(item.order_qty || 0) -
                                        Number(item.discount_amount || 0),
                                ),
                            )
                        }}
                    </div>
                </div>
                <div class="grid content-start gap-1.5">
                    <span class="hidden h-5 xl:block" /><Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        class="size-9 text-destructive"
                        title="Remove item"
                        @click="form.items.splice(index, 1)"
                        ><Trash2 class="size-4" /><span class="sr-only"
                            >Remove item</span
                        ></Button
                    >
                </div>
            </div>
            <InputError :message="form.errors.items" />
        </div>

        <div class="grid gap-5 rounded-md border p-4 sm:p-5 lg:grid-cols-3">
            <div class="grid gap-4 lg:col-span-2">
                <div class="grid gap-1.5">
                    <Label for="order_note">Note</Label
                    ><textarea
                        id="order_note"
                        v-model="form.note"
                        name="note"
                        rows="4"
                        placeholder="Optional order note"
                        class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                    /><InputError :message="form.errors.note" />
                </div>
            </div>
            <div class="space-y-3 rounded-md bg-muted/40 p-4">
                <div class="flex justify-between text-sm">
                    <span>Subtotal</span><span>{{ money(subtotal) }}</span>
                </div>
                <div>
                    <div class="flex items-center justify-between gap-4">
                        <Label for="order_discount">Order discount</Label>
                        <Input
                            id="order_discount"
                            v-model="form.discount_amount"
                            type="number"
                            min="0"
                            step="0.01"
                            name="discount_amount"
                            class="h-8 w-28 text-right"
                        />
                    </div>
                    <InputError
                        class="mt-1 text-right"
                        :message="form.errors.discount_amount"
                    />
                </div>
                <div>
                    <div class="flex items-center justify-between gap-4">
                        <Label for="delivery_charge">Delivery charge</Label>
                        <Input
                            id="delivery_charge"
                            v-model="form.delivery_charge"
                            type="number"
                            min="0"
                            step="0.01"
                            name="delivery_charge"
                            class="h-8 w-28 text-right"
                        />
                    </div>
                    <InputError
                        class="mt-1 text-right"
                        :message="form.errors.delivery_charge"
                    />
                </div>
                <div class="flex justify-between border-t pt-3 font-semibold">
                    <span>Total</span><span>{{ money(total) }}</span>
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-3">
            <Button
                type="button"
                variant="outline"
                @click="router.visit('/orders')"
                >Cancel</Button
            ><Button type="submit" :disabled="form.processing">{{
                form.processing
                    ? 'Saving...'
                    : order
                      ? 'Update order'
                      : 'Create order'
            }}</Button>
        </div>

        <Dialog v-model:open="customerPickerOpen"
            ><DialogContent class="sm:max-w-xl"
                ><DialogHeader
                    ><DialogTitle>Choose customer</DialogTitle
                    ><DialogDescription
                        >Search and choose a customer for this
                        order.</DialogDescription
                    ></DialogHeader
                >
                <div class="relative">
                    <Search
                        class="absolute top-2.5 left-3 size-4 text-muted-foreground"
                    /><Input
                        v-model="customerSearch"
                        class="pl-9"
                        placeholder="Search customers"
                        autofocus
                    />
                </div>
                <div class="max-h-80 space-y-2 overflow-y-auto">
                    <button
                        v-for="customer in filteredCustomers"
                        :key="customer.id"
                        type="button"
                        class="flex w-full items-center gap-3 rounded-md border p-3 text-left hover:bg-muted/50"
                        @click="chooseCustomer(customer.id)"
                    >
                        <UserRound class="size-8 text-muted-foreground" />
                        <div class="min-w-0 flex-1">
                            <p class="font-medium">{{ customer.name }}</p>
                            <p class="truncate text-sm text-muted-foreground">
                                {{
                                    customer.phone ||
                                    customer.email ||
                                    'No contact details'
                                }}
                            </p>
                        </div>
                        <Check
                            v-if="
                                String(customer.id) === String(form.customer_id)
                            "
                            class="size-4 text-primary"
                        />
                    </button>
                    <p
                        v-if="!filteredCustomers.length"
                        class="py-8 text-center text-sm text-muted-foreground"
                    >
                        No customers found.
                    </p>
                </div></DialogContent
            ></Dialog
        >
        <Dialog v-model:open="productPickerOpen"
            ><DialogContent class="sm:max-w-2xl"
                ><DialogHeader
                    ><DialogTitle>Add product</DialogTitle
                    ><DialogDescription
                        >Search available products to add to this
                        order.</DialogDescription
                    ></DialogHeader
                >
                <div class="relative">
                    <Search
                        class="absolute top-2.5 left-3 size-4 text-muted-foreground"
                    /><Input
                        v-model="productSearch"
                        class="pl-9"
                        placeholder="Search products"
                        autofocus
                    />
                </div>
                <div class="max-h-96 space-y-2 overflow-y-auto">
                    <button
                        v-for="product in filteredProducts"
                        :key="product.id"
                        type="button"
                        :disabled="
                            productIsAdded(product.id) ||
                            product.stock_quantity < 1
                        "
                        class="flex w-full items-center gap-3 rounded-md border p-3 text-left hover:bg-muted/50 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="chooseProduct(product)"
                    >
                        <Package class="size-8 text-muted-foreground" />
                        <div class="min-w-0 flex-1">
                            <p class="font-medium">{{ product.name }}</p>
                            <p class="text-sm text-muted-foreground">
                                {{ product.sku || 'No SKU' }} · Stock
                                {{ product.stock_quantity }}
                                {{ product.unit || 'pcs' }} ·
                                {{
                                    money(
                                        product.sale_price ||
                                            product.regular_price,
                                    )
                                }}
                            </p>
                        </div>
                        <Check
                            v-if="productIsAdded(product.id)"
                            class="size-4 text-primary"
                        />
                    </button>
                    <p
                        v-if="!filteredProducts.length"
                        class="py-8 text-center text-sm text-muted-foreground"
                    >
                        No products found.
                    </p>
                </div></DialogContent
            ></Dialog
        >
    </form>
</template>
