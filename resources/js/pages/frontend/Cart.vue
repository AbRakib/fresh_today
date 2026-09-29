<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronRight, ShoppingBag, ShoppingCart, Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import SiteFooter from '@/components/site/SiteFooter.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';
import { useCurrency } from '@/composables/useCurrency';

type CartProduct = {
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
    discount_percentage: string | null;
    badge: string | null;
    stock_quantity: number;
    quantity: number;
    line_total: string;
};

const props = defineProps<{
    cart_products: CartProduct[];
}>();

const { money } = useCurrency();
const productUrl = (product: CartProduct) => `/product/${product.slug}`;
const imageUrl = (text: string) =>
    `https://placehold.co/520x420/f5f7f4/23833f?text=${encodeURIComponent(text)}`;
const dealPrice = (product: CartProduct) =>
    product.sale_price || product.regular_price;
const hasSalePrice = (product: CartProduct) =>
    Boolean(product.sale_price) && product.sale_price !== product.regular_price;
const productUnit = (product: CartProduct) =>
    [product.weight || product.gross_weight, product.unit]
        .filter(Boolean)
        .join(' ') || 'Per item';
const cartTotal = computed(() =>
    props.cart_products.reduce(
        (total, product) => total + Number(product.line_total),
        0,
    ),
);
const removeFromCart = (product: CartProduct) => {
    router.delete(`/cart/${product.id}`, {
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Head title="My Cart">
        <link rel="preconnect" href="https://placehold.co" />
    </Head>

    <div class="min-h-screen bg-[#f7faf7] text-[#213228]">
        <SiteHeader />

        <main class="mx-auto w-[min(1180px,calc(100%-32px))] py-8">
            <div class="mb-6 flex items-center gap-2 text-xs text-slate-500">
                <Link href="/" class="hover:text-[#218a37]">Home</Link>
                <ChevronRight class="h-3 w-3" />
                <span class="text-slate-700">Cart</span>
            </div>

            <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-[#1b2f24] md:text-4xl">
                        My Cart
                    </h1>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ cart_products.length }} cart
                        {{ cart_products.length === 1 ? 'item' : 'items' }}
                    </p>
                </div>
                <Link
                    href="/shop"
                    class="rounded bg-lime-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-lime-600"
                >
                    Continue Shopping
                </Link>
            </div>

            <div
                v-if="cart_products.length"
                class="grid gap-5 lg:grid-cols-[1fr_320px]"
            >
                <section class="space-y-3">
                    <article
                        v-for="product in cart_products"
                        :key="product.id"
                        class="grid gap-4 rounded-lg border border-slate-200 bg-white p-3 shadow-sm sm:grid-cols-[140px_1fr_auto]"
                    >
                        <Link :href="productUrl(product)" class="block">
                            <img
                                :src="
                                    product.thumbnail_url ??
                                    imageUrl(product.name)
                                "
                                :alt="product.name"
                                class="h-32 w-full rounded-md object-cover sm:h-28"
                            />
                        </Link>

                        <div>
                            <p class="text-[11px] font-semibold text-[#218a37]">
                                {{ product.category_name || 'Fresh Today' }}
                            </p>
                            <Link
                                :href="productUrl(product)"
                                class="mt-1 block font-bold text-slate-800 hover:text-[#218a37]"
                            >
                                {{ product.name }}
                            </Link>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ productUnit(product) }}
                            </p>
                            <div class="mt-3 flex flex-wrap items-end gap-2">
                                <span class="text-lg font-black text-[#218a37]">
                                    {{ money(dealPrice(product)) }}
                                </span>
                                <span
                                    v-if="hasSalePrice(product)"
                                    class="text-xs text-red-600 line-through"
                                >
                                    {{ money(product.regular_price) }}
                                </span>
                                <span class="text-xs text-slate-500">
                                    x {{ product.quantity }}
                                </span>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between gap-4 sm:flex-col sm:items-end"
                        >
                            <strong class="text-lg text-[#1b2f24]">
                                {{ money(product.line_total) }}
                            </strong>
                            <button
                                type="button"
                                class="inline-flex min-h-10 items-center justify-center gap-2 rounded border border-red-200 px-3 text-xs font-bold text-red-500 hover:bg-red-50"
                                :aria-label="`Remove ${product.name} from cart`"
                                @click="removeFromCart(product)"
                            >
                                <Trash2 class="h-4 w-4" />
                                Remove
                            </button>
                        </div>
                    </article>
                </section>

                <aside class="h-fit rounded-lg bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-black text-[#1b2f24]">
                        Order Summary
                    </h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal</span>
                            <strong class="text-slate-900">{{
                                money(cartTotal)
                            }}</strong>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Delivery</span>
                            <span>Calculated later</span>
                        </div>
                    </div>
                    <div
                        class="mt-5 flex justify-between border-t border-slate-100 pt-4 text-base font-black"
                    >
                        <span>Total</span>
                        <span class="text-[#218a37]">{{
                            money(cartTotal)
                        }}</span>
                    </div>
                    <Link
                        href="/checkout"
                        class="mt-5 flex min-h-11 w-full items-center justify-center gap-2 rounded bg-[#218a37] px-5 text-sm font-bold text-white hover:bg-[#176536]"
                    >
                        <ShoppingBag class="h-4 w-4" />
                        Checkout
                    </Link>
                </aside>
            </div>

            <section
                v-else
                class="border-y border-slate-200 bg-white px-6 py-16 text-center"
            >
                <ShoppingCart class="mx-auto h-12 w-12 text-slate-300" />
                <h2 class="mt-4 text-xl font-black text-[#1b2f24]">
                    Your cart is empty
                </h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-600">
                    Add fresh products to your cart and review them here before
                    checkout.
                </p>
                <Link
                    href="/shop"
                    class="mt-6 inline-flex rounded bg-lime-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-lime-600"
                >
                    Browse Products
                </Link>
            </section>
        </main>

        <SiteFooter />
    </div>
</template>
