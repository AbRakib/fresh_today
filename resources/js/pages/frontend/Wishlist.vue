<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ChevronRight,
    Heart,
    ShoppingBag,
    ShoppingCart,
    Trash2,
} from '@lucide/vue';
import SiteFooter from '@/components/site/SiteFooter.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';
import { useCurrency } from '@/composables/useCurrency';

type WishlistProduct = {
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
    is_in_cart: boolean;
};

defineProps<{
    wishlist_products: WishlistProduct[];
}>();

const { money } = useCurrency();
const productUrl = (product: WishlistProduct) => `/product/${product.slug}`;
const imageUrl = (text: string) =>
    `https://placehold.co/520x420/f5f7f4/23833f?text=${encodeURIComponent(text)}`;
const dealPrice = (product: WishlistProduct) =>
    product.sale_price || product.regular_price;
const hasSalePrice = (product: WishlistProduct) =>
    Boolean(product.sale_price) && product.sale_price !== product.regular_price;
const productUnit = (product: WishlistProduct) =>
    [product.weight || product.gross_weight, product.unit]
        .filter(Boolean)
        .join(' ') || 'Per item';
const discountLabel = (product: WishlistProduct) => {
    const discount = Number(product.discount_percentage ?? 0);

    if (discount > 0) {
        return `${Number.isInteger(discount) ? discount : discount.toFixed(1)}% OFF`;
    }

    return product.badge;
};
const removeFromWishlist = (product: WishlistProduct) => {
    router.delete(`/wishlist/${product.id}`, {
        preserveScroll: true,
        preserveState: true,
    });
};
const addToCart = (product: WishlistProduct) => {
    if (product.is_in_cart) {
        router.visit('/cart');
        return;
    }

    router.post(
        `/cart/${product.id}`,
        {},
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};
const checkoutProduct = (product: WishlistProduct) => {
    if (product.is_in_cart) {
        router.visit('/checkout');
        return;
    }

    router.post(
        `/cart/${product.id}`,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => router.visit('/checkout'),
        },
    );
};
</script>

<template>
    <Head title="My Wishlist">
        <link rel="preconnect" href="https://placehold.co" />
    </Head>

    <div class="min-h-screen bg-[#f7faf7] text-[#213228]">
        <SiteHeader />

        <main class="mx-auto w-[min(1180px,calc(100%-32px))] py-8">
            <div class="mb-6 flex items-center gap-2 text-xs text-slate-500">
                <Link href="/" class="hover:text-[#218a37]">Home</Link>
                <ChevronRight class="h-3 w-3" />
                <span class="text-slate-700">Wishlist</span>
            </div>

            <div class="mb-7 flex items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-[#1b2f24] md:text-4xl">
                        My Wishlist
                    </h1>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ wishlist_products.length }} saved
                        {{
                            wishlist_products.length === 1
                                ? 'product'
                                : 'products'
                        }}
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
                v-if="wishlist_products.length"
                class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4"
            >
                <article
                    v-for="product in wishlist_products"
                    :key="product.id"
                    class="relative rounded-lg border border-slate-200 bg-white p-2 shadow-sm"
                >
                    <span
                        v-if="discountLabel(product)"
                        class="absolute top-3 left-3 z-10 rounded bg-red-500 px-2 py-1 text-[10px] font-black text-white"
                    >
                        {{ discountLabel(product) }}
                    </span>
                    <button
                        type="button"
                        class="absolute top-3 right-3 z-10 grid h-8 w-8 place-items-center rounded-full bg-white text-red-500 shadow hover:bg-red-50"
                        :aria-label="`Remove ${product.name} from wishlist`"
                        @click="removeFromWishlist(product)"
                    >
                        <Trash2 class="h-4 w-4" />
                    </button>

                    <Link :href="productUrl(product)" class="block">
                        <img
                            :src="
                                product.thumbnail_url ?? imageUrl(product.name)
                            "
                            :alt="product.name"
                            class="h-44 w-full rounded-md object-cover sm:h-52"
                        />
                    </Link>

                    <div class="p-2">
                        <p class="text-[11px] font-semibold text-[#218a37]">
                            {{ product.category_name || 'Fresh Today' }}
                        </p>
                        <Link
                            :href="productUrl(product)"
                            class="mt-1 block text-sm font-bold text-slate-800 hover:text-[#218a37]"
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
                        </div>
                        <div class="mt-3 grid gap-2">
                            <button
                                type="button"
                                class="flex w-full items-center justify-center gap-2 rounded border border-lime-500 py-2 text-xs font-bold text-lime-600 hover:bg-lime-500 hover:text-white disabled:cursor-not-allowed disabled:opacity-50"
                                :class="
                                    product.is_in_cart
                                        ? 'border-[#176536] bg-[#176536] text-white hover:bg-[#0f4b27]'
                                        : ''
                                "
                                :disabled="
                                    product.stock_quantity < 1 &&
                                    !product.is_in_cart
                                "
                                @click="addToCart(product)"
                            >
                                <ShoppingCart class="h-4 w-4" />
                                {{
                                    product.is_in_cart
                                        ? 'View cart'
                                        : product.stock_quantity > 0
                                          ? 'Add to cart'
                                          : 'Out of stock'
                                }}
                            </button>
                            <button
                                type="button"
                                class="flex w-full items-center justify-center gap-2 rounded bg-[#218a37] py-2 text-xs font-bold text-white hover:bg-[#176536] disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="
                                    product.stock_quantity < 1 &&
                                    !product.is_in_cart
                                "
                                @click="checkoutProduct(product)"
                            >
                                <ShoppingBag class="h-4 w-4" />
                                Checkout
                            </button>
                        </div>
                    </div>
                </article>
            </div>

            <section
                v-else
                class="border-y border-slate-200 bg-white px-6 py-16 text-center"
            >
                <Heart class="mx-auto h-12 w-12 text-slate-300" />
                <h2 class="mt-4 text-xl font-black text-[#1b2f24]">
                    Your wishlist is empty
                </h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-600">
                    Save products you love and find them here whenever you are
                    ready.
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
