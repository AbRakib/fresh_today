<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ChevronRight,
    Heart,
    PackageCheck,
    ShieldCheck,
    ShoppingCart,
    Truck,
} from '@lucide/vue';
import SiteFooter from '@/components/site/SiteFooter.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';
import { useCurrency } from '@/composables/useCurrency';

type Product = {
    id: number;
    category_name: string | null;
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
    regular_price: string;
    sale_price: string | null;
    discount_percentage: string | null;
    badge: string | null;
    stock_quantity: number;
    minimum_order_quantity: number;
};

const props = defineProps<{
    product: Product;
}>();

const { money } = useCurrency();

const imageUrl = (text: string) =>
    `https://placehold.co/720x560/f7f8f5/2f7d45?text=${encodeURIComponent(text)}`;
const dealPrice = props.product.sale_price || props.product.regular_price;
const hasSalePrice =
    Boolean(props.product.sale_price) &&
    props.product.sale_price !== props.product.regular_price;
const discount = Number(props.product.discount_percentage ?? 0);
const badge =
    discount > 0
        ? `${Number.isInteger(discount) ? discount : discount.toFixed(1)}% OFF`
        : props.product.badge;
const productUnit =
    [props.product.weight || props.product.gross_weight, props.product.unit]
        .filter(Boolean)
        .join(' ') || 'Per item';
</script>

<template>
    <Head :title="product.name">
        <link rel="preconnect" href="https://placehold.co" />
    </Head>

    <div class="min-h-screen bg-[#f7faf7] text-[#213228]">
        <SiteHeader />

        <main class="mx-auto w-[min(1180px,calc(100%-32px))] py-8">
            <div class="mb-6 flex items-center gap-2 text-xs text-slate-500">
                <Link href="/" class="hover:text-[#218a37]">Home</Link>
                <ChevronRight class="h-3 w-3" />
                <Link href="/fresh-fish" class="hover:text-[#218a37]">
                    Fresh Fish
                </Link>
                <ChevronRight class="h-3 w-3" />
                <span class="text-slate-700">{{ product.name }}</span>
            </div>

            <section
                class="grid gap-8 rounded-lg bg-white p-4 shadow-sm md:grid-cols-[1fr_1fr] md:p-6"
            >
                <div
                    class="relative overflow-hidden rounded-lg border border-slate-100 bg-slate-50"
                >
                    <span
                        v-if="badge"
                        class="absolute top-4 left-4 z-10 rounded bg-red-500 px-3 py-1 text-xs font-black text-white"
                    >
                        {{ badge }}
                    </span>
                    <img
                        :src="product.thumbnail_url ?? imageUrl(product.name)"
                        :alt="product.name"
                        class="h-[360px] w-full object-cover md:h-[520px]"
                    />
                </div>

                <div class="flex flex-col justify-center py-2">
                    <p
                        class="text-xs font-semibold tracking-wide text-[#218a37] uppercase"
                    >
                        {{ product.category_name || 'Fresh Today' }}
                    </p>
                    <h1
                        class="mt-2 text-3xl font-black text-[#1b2f24] md:text-4xl"
                    >
                        {{ product.name }}
                    </h1>
                    <p
                        v-if="product.short_description"
                        class="mt-4 text-sm leading-6 text-slate-600"
                    >
                        {{ product.short_description }}
                    </p>

                    <div class="mt-6 flex flex-wrap items-end gap-3">
                        <span
                            v-if="hasSalePrice"
                            class="text-base text-slate-400 line-through"
                        >
                            {{ money(product.regular_price) }}
                        </span>
                        <span class="text-3xl font-black text-[#218a37]">
                            {{ money(dealPrice) }}
                        </span>
                        <span class="pb-1 text-sm text-slate-500"
                            >/{{ productUnit }}</span
                        >
                    </div>

                    <div
                        class="mt-6 grid gap-3 text-sm text-slate-700 sm:grid-cols-2"
                    >
                        <div
                            class="rounded border border-slate-100 bg-[#f7faf7] p-3"
                        >
                            <span class="block text-xs text-slate-500"
                                >Stock</span
                            >
                            <strong
                                >{{ product.stock_quantity }} available</strong
                            >
                        </div>
                        <div
                            class="rounded border border-slate-100 bg-[#f7faf7] p-3"
                        >
                            <span class="block text-xs text-slate-500"
                                >Minimum order</span
                            >
                            <strong
                                >{{ product.minimum_order_quantity }}
                                {{ product.unit || 'item' }}</strong
                            >
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button
                            class="flex min-h-11 flex-1 items-center justify-center gap-2 rounded bg-lime-500 px-5 text-sm font-bold text-white hover:bg-lime-600 sm:flex-none"
                        >
                            <ShoppingCart class="h-4 w-4" />
                            Add to Cart
                        </button>
                        <button
                            class="grid min-h-11 w-12 place-items-center rounded border border-slate-200 text-slate-500 hover:border-lime-500 hover:text-lime-600"
                            aria-label="Add to wishlist"
                        >
                            <Heart class="h-5 w-5" />
                        </button>
                    </div>

                    <div
                        class="mt-7 grid gap-3 text-xs text-slate-600 sm:grid-cols-3"
                    >
                        <div class="flex items-center gap-2">
                            <Truck class="h-5 w-5 text-[#218a37]" /> Fast
                            delivery
                        </div>
                        <div class="flex items-center gap-2">
                            <PackageCheck class="h-5 w-5 text-[#218a37]" />
                            Hygienic pack
                        </div>
                        <div class="flex items-center gap-2">
                            <ShieldCheck class="h-5 w-5 text-[#218a37]" />
                            Quality checked
                        </div>
                    </div>
                </div>
            </section>

            <section
                v-if="product.description"
                class="mt-6 rounded-lg bg-white p-5 shadow-sm"
            >
                <h2 class="text-lg font-black text-[#1b2f24]">
                    Product Details
                </h2>
                <div
                    class="mt-3 text-sm leading-7 text-slate-600"
                    v-html="product.description"
                ></div>
            </section>
        </main>

        <SiteFooter />
    </div>
</template>
