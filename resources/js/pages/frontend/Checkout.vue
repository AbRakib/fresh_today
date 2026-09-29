<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ChevronRight, MapPin, PackageCheck, ShoppingCart } from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import SiteFooter from '@/components/site/SiteFooter.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';
import { useCurrency } from '@/composables/useCurrency';

type CheckoutProduct = {
    id: number;
    category_name: string | null;
    name: string;
    slug: string;
    thumbnail_url: string | null;
    unit: string | null;
    gross_weight: string | null;
    weight: string | null;
    regular_price: string;
    sale_price: string | null;
    quantity: number;
    line_total: string;
};

type DeliveryCharge = {
    id: number;
    title: string;
    amount: string;
};

type FrontendCustomer = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    address?: string | null;
};

const props = defineProps<{
    cart_products: CheckoutProduct[];
    delivery_charges: DeliveryCharge[];
}>();

const page = usePage<{
    auth: {
        customer: FrontendCustomer | null;
    };
}>();
const { money } = useCurrency();
const customer = computed(() => page.props.auth.customer);
const defaultDeliveryCharge = computed(() => props.delivery_charges[0]);
const form = useForm({
    name: customer.value?.name ?? '',
    phone: customer.value?.phone ?? '',
    delivery_charge_id: defaultDeliveryCharge.value?.id ?? '',
    delivery_address: customer.value?.address ?? '',
    note: '',
});
const imageUrl = (text: string) =>
    `https://placehold.co/520x420/f5f7f4/23833f?text=${encodeURIComponent(text)}`;
const productUrl = (product: CheckoutProduct) => `/product/${product.slug}`;
const productUnit = (product: CheckoutProduct) =>
    [product.weight || product.gross_weight, product.unit]
        .filter(Boolean)
        .join(' ') || 'Per item';
const subtotal = computed(() =>
    props.cart_products.reduce(
        (total, product) => total + Number(product.line_total),
        0,
    ),
);
const selectedDeliveryCharge = computed(() =>
    props.delivery_charges.find(
        (charge) => String(charge.id) === String(form.delivery_charge_id),
    ),
);
const deliveryAmount = computed(() =>
    Number(selectedDeliveryCharge.value?.amount ?? 0),
);
const cartError = computed(
    () => (form.errors as Record<string, string | undefined>).cart,
);
const total = computed(() => subtotal.value + deliveryAmount.value);
const placeOrder = () => {
    form.post('/checkout', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Checkout">
        <link rel="preconnect" href="https://placehold.co" />
    </Head>

    <div class="min-h-screen bg-[#f7faf7] text-[#213228]">
        <SiteHeader />

        <main class="mx-auto w-[min(1180px,calc(100%-32px))] py-8">
            <div class="mb-6 flex items-center gap-2 text-xs text-slate-500">
                <Link href="/" class="hover:text-[#218a37]">Home</Link>
                <ChevronRight class="h-3 w-3" />
                <Link href="/cart" class="hover:text-[#218a37]">Cart</Link>
                <ChevronRight class="h-3 w-3" />
                <span class="text-slate-700">Checkout</span>
            </div>

            <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-[#1b2f24] md:text-4xl">
                        Checkout
                    </h1>
                    <p class="mt-2 text-sm text-slate-600">
                        Confirm delivery details and place your order.
                    </p>
                </div>
                <Link
                    href="/cart"
                    class="rounded border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:border-[#218a37] hover:text-[#218a37]"
                >
                    Back to Cart
                </Link>
            </div>

            <form
                class="grid gap-5 lg:grid-cols-[1fr_360px]"
                @submit.prevent="placeOrder"
            >
                <section>
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-2">
                            <MapPin class="h-5 w-5 text-[#218a37]" />
                            <h2 class="text-lg font-black text-[#1b2f24]">
                                Delivery Details
                            </h2>
                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-bold text-slate-700"
                                    for="customer-name"
                                >
                                    Name <span class="text-red-600">*</span>
                                </label>
                                <input
                                    id="customer-name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    autocomplete="name"
                                    class="h-11 w-full rounded border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-[#218a37] focus:ring-1 focus:ring-[#dcefdc]"
                                />
                                <InputError :message="form.errors.name" />
                            </div>
                            <div>
                                <label
                                    class="mb-2 block text-sm font-bold text-slate-700"
                                    for="customer-phone"
                                >
                                    Phone <span class="text-red-600">*</span>
                                </label>
                                <input
                                    id="customer-phone"
                                    v-model="form.phone"
                                    type="tel"
                                    required
                                    autocomplete="tel"
                                    class="h-11 w-full rounded border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-[#218a37] focus:ring-1 focus:ring-[#dcefdc]"
                                />
                                <InputError :message="form.errors.phone" />
                            </div>
                        </div>

                        <div class="mt-4">
                            <label
                                class="mb-2 block text-sm font-bold text-slate-700"
                                for="delivery-address"
                            >
                                Delivery Address
                                <span class="text-red-600">*</span>
                            </label>
                            <textarea
                                id="delivery-address"
                                v-model="form.delivery_address"
                                rows="4"
                                required
                                class="w-full rounded border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#218a37] focus:ring-1 focus:ring-[#dcefdc]"
                                placeholder="House, road, area, city"
                            />
                            <InputError
                                :message="form.errors.delivery_address"
                            />
                        </div>

                        <div class="mt-4">
                            <label
                                class="mb-2 block text-sm font-bold text-slate-700"
                                for="delivery-charge"
                            >
                                Delivery Charge
                                <span class="text-red-600">*</span>
                            </label>
                            <select
                                id="delivery-charge"
                                v-model="form.delivery_charge_id"
                                required
                                class="h-11 w-full rounded border border-slate-200 bg-white px-3 text-sm outline-none focus:border-[#218a37] focus:ring-1 focus:ring-[#dcefdc]"
                            >
                                <option value="" disabled>
                                    Select delivery area
                                </option>
                                <option
                                    v-for="charge in delivery_charges"
                                    :key="charge.id"
                                    :value="charge.id"
                                >
                                    {{ charge.title }} -
                                    {{ money(charge.amount) }}
                                </option>
                            </select>
                            <InputError
                                :message="form.errors.delivery_charge_id"
                            />
                        </div>

                        <div class="mt-4">
                            <label
                                class="mb-2 block text-sm font-bold text-slate-700"
                                for="order-note"
                            >
                                Order Note
                            </label>
                            <textarea
                                id="order-note"
                                v-model="form.note"
                                rows="3"
                                class="w-full rounded border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#218a37] focus:ring-1 focus:ring-[#dcefdc]"
                                placeholder="Any special delivery instruction"
                            />
                            <InputError :message="form.errors.note" />
                        </div>
                    </div>
                </section>

                <aside class="h-fit space-y-5">
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-2">
                            <ShoppingCart class="h-5 w-5 text-[#218a37]" />
                            <h2 class="text-lg font-black text-[#1b2f24]">
                                Order Items
                            </h2>
                        </div>

                        <div class="mt-5 space-y-4">
                            <article
                                v-for="product in cart_products"
                                :key="product.id"
                                class="grid grid-cols-[72px_minmax(0,1fr)] gap-3 border-b border-slate-100 pb-4 last:border-0 last:pb-0"
                            >
                                <Link :href="productUrl(product)">
                                    <img
                                        :src="
                                            product.thumbnail_url ??
                                            imageUrl(product.name)
                                        "
                                        :alt="product.name"
                                        class="h-[72px] w-[72px] rounded object-cover"
                                    />
                                </Link>
                                <div class="min-w-0">
                                    <p
                                        class="text-[11px] font-semibold text-[#218a37]"
                                    >
                                        {{
                                            product.category_name ||
                                            'Fresh Today'
                                        }}
                                    </p>
                                    <Link
                                        :href="productUrl(product)"
                                        class="mt-1 block text-sm font-bold text-slate-800 hover:text-[#218a37]"
                                    >
                                        {{ product.name }}
                                    </Link>
                                    <div
                                        class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs"
                                    >
                                        <span class="text-slate-500">
                                            {{ productUnit(product) }} x
                                            {{ product.quantity }}
                                        </span>
                                        <strong class="text-[#1b2f24]">
                                            {{ money(product.line_total) }}
                                        </strong>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-black text-[#1b2f24]">
                            Payment Summary
                        </h2>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal</span>
                                <strong class="text-slate-900">{{
                                    money(subtotal)
                                }}</strong>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Delivery</span>
                                <strong class="text-slate-900">{{
                                    money(deliveryAmount)
                                }}</strong>
                            </div>
                        </div>
                        <div
                            class="mt-5 flex justify-between border-t border-slate-100 pt-4 text-base font-black"
                        >
                            <span>Total</span>
                            <span class="text-[#218a37]">{{
                                money(total)
                            }}</span>
                        </div>
                        <p class="mt-3 text-xs text-slate-500">
                            Payment method: Cash on delivery
                        </p>
                        <button
                            type="submit"
                            class="mt-5 flex min-h-11 w-full items-center justify-center gap-2 rounded bg-[#218a37] px-5 text-sm font-bold text-white hover:bg-[#176536] disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="
                                form.processing || !delivery_charges.length
                            "
                        >
                            <PackageCheck class="h-4 w-4" />
                            Place Order
                        </button>
                        <InputError class="mt-3" :message="cartError" />
                    </div>
                </aside>
            </form>
        </main>

        <SiteFooter />
    </div>
</template>
