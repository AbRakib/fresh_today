<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    Bike,
    ChevronLeft,
    ChevronRight,
    Fish,
    Leaf,
    PackageCheck,
    ShieldCheck,
    ShoppingBag,
    ShoppingCart,
    Truck,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import SiteFooter from '@/components/site/SiteFooter.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';
import { useCurrency } from '@/composables/useCurrency';

type Product = {
    id: number;
    name: string;
    slug: string;
    thumbnail_url: string | null;
    short_description: string | null;
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

type FrontendCategory = {
    id: number;
    name: string;
    icon_url: string | null;
};

type FrontendSlider = {
    id: number;
    image_url: string;
    button: string | null;
    product: { name: string; slug: string } | null;
};

const page = usePage<{
    frontend_categories?: FrontendCategory[];
    frontend_products?: Product[];
    frontend_sliders?: FrontendSlider[];
}>();
const categories = computed(() => page.props.frontend_categories ?? []);
const products = computed(() => page.props.frontend_products ?? []);
const sliders = computed(() => page.props.frontend_sliders ?? []);
const activeSlide = ref(0);
const currentSlide = computed(() => sliders.value[activeSlide.value] ?? null);

const selectSlide = (index: number) => {
    activeSlide.value = index;
};

const previousSlide = () => {
    activeSlide.value =
        (activeSlide.value - 1 + sliders.value.length) % sliders.value.length;
};

const nextSlide = () => {
    activeSlide.value = (activeSlide.value + 1) % sliders.value.length;
};

const { money } = useCurrency();
const displayPrice = (value: string | null) => money(value ?? 0);
const dealPrice = (product: Product) =>
    product.sale_price || product.regular_price;
const showRegularPrice = (product: Product) =>
    Boolean(product.sale_price) && product.sale_price !== product.regular_price;
const discountLabel = (product: Product) => {
    const discount = Number(product.discount_percentage ?? 0);

    if (discount > 0) {
        return `${Number.isInteger(discount) ? discount : discount.toFixed(1)}% OFF`;
    }

    return product.badge || 'Fresh';
};
const productUrl = (product: Product) => '/product/' + product.slug;
const toggleCart = (product: Product) => {
    const options = { preserveScroll: true, preserveState: true };

    if (product.is_in_cart) {
        router.visit('/cart');

        return;
    }

    router.post('/cart/' + product.id, {}, options);
};

const formatWeightLabel = (
    amount: string | null,
    unit: string | null,
): string => {
    if (!amount) {
        return unit ?? '';
    }

    const trimmedAmount = amount.trim();
    const trimmedUnit = unit?.trim();

    if (!trimmedUnit) {
        return trimmedAmount.replace(/^([\d.,]+)\s*([a-zA-Z]+)$/, '$1 $2');
    }

    const amountAlreadyHasUnit = new RegExp(
        `\\s*${trimmedUnit.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}$`,
        'i',
    ).test(trimmedAmount);

    return amountAlreadyHasUnit
        ? trimmedAmount.replace(/^([\d.,]+)\s*([a-zA-Z]+)$/, '$1 $2')
        : `${trimmedAmount} ${trimmedUnit}`;
};

const netWeightLabel = (product: Product) =>
    formatWeightLabel(product.weight, product.unit);

const productUnit = (product: Product) => {
    const amount = product.weight || product.gross_weight;

    return formatWeightLabel(amount, product.unit) || product.unit || 'item';
};

const imageUrl = (text: string, size = '500x360') =>
    `https://placehold.co/${size}/f7f8f5/2f7d45?text=${encodeURIComponent(text)}`;
</script>

<template>
    <Head title="Fresh Today">
        <link rel="preconnect" href="https://placehold.co" />
    </Head>

    <div class="min-h-screen bg-white font-sans text-slate-800">
        <SiteHeader />

        <main>
            <section
                v-if="currentSlide"
                class="mx-auto w-[min(1180px,calc(100%-32px))] pt-5"
            >
                <div
                    class="relative overflow-hidden rounded-2xl bg-slate-50 shadow-[0_8px_30px_rgba(0,0,0,.07)]"
                >
                    <img
                        :key="currentSlide.id"
                        :src="currentSlide.image_url"
                        :alt="currentSlide.product?.name ?? 'Promotion'"
                        class="aspect-[16/7] w-full object-cover"
                    />

                    <Link
                        v-if="currentSlide.product"
                        :href="`/product/${currentSlide.product.slug}`"
                        class="absolute bottom-5 left-5 rounded-md bg-lime-500 px-5 py-2.5 text-sm font-bold text-white shadow transition hover:bg-lime-600 sm:bottom-7 sm:left-7"
                    >
                        {{ currentSlide.button || currentSlide.product.name }}
                    </Link>

                    <button
                        v-if="sliders.length > 1"
                        type="button"
                        class="absolute top-1/2 left-3 grid h-9 w-9 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-slate-700 shadow"
                        aria-label="Previous slide"
                        @click="previousSlide"
                    >
                        <ChevronLeft class="h-5 w-5" />
                    </button>
                    <button
                        v-if="sliders.length > 1"
                        type="button"
                        class="absolute top-1/2 right-3 grid h-9 w-9 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-slate-700 shadow"
                        aria-label="Next slide"
                        @click="nextSlide"
                    >
                        <ChevronRight class="h-5 w-5" />
                    </button>
                </div>

                <div v-if="sliders.length > 1" class="mt-3 flex justify-center gap-2">
                    <button
                        v-for="(slider, index) in sliders"
                        :key="slider.id"
                        type="button"
                        class="h-2 w-2 rounded-full transition"
                        :class="index === activeSlide ? 'bg-[#207f42]' : 'bg-slate-300'"
                        :aria-label="`Show slide ${index + 1}`"
                        :aria-current="index === activeSlide ? 'true' : undefined"
                        @click="selectSlide(index)"
                    />
                </div>
            </section>

            <section
                id="categories"
                class="mx-auto w-[min(1180px,calc(100%-32px))] py-5"
            >
                <div class="rounded-2xl bg-slate-50 p-5 sm:p-8">
                    <div class="mb-6 flex items-center justify-center gap-3">
                        <span class="h-px w-8 bg-[#5eba78]"></span>
                        <h2 class="text-xl font-black text-[#124327]">
                            Shop by Category
                        </h2>
                        <span class="h-px w-8 bg-[#5eba78]"></span>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5"
                    >
                        <Link
                            v-for="category in categories"
                            :key="category.id"
                            href="/shop"
                            class="group rounded-xl border border-slate-200 bg-white p-3 text-center shadow-[0_4px_16px_rgba(0,0,0,.08)] transition hover:-translate-y-1 hover:border-[#97d6a8]"
                        >
                            <div class="relative rounded-lg bg-slate-50 pb-5">
                                <div class="overflow-hidden rounded-lg">
                                    <img
                                        v-if="category.icon_url"
                                        :src="category.icon_url"
                                        :alt="category.name"
                                        class="h-32 w-full object-cover transition duration-300 group-hover:scale-105"
                                    />
                                    <div
                                        v-else
                                        class="flex h-32 w-full items-center justify-center bg-[#f7f8f5] px-4 text-center text-lg font-black text-[#2f7d45]"
                                    >
                                        {{ category.name }}
                                    </div>
                                </div>
                                <span
                                    class="absolute bottom-1 left-1/2 grid h-9 w-9 -translate-x-1/2 translate-y-1/2 place-items-center rounded-full border border-[#97d6a8] bg-white text-[#176536] shadow"
                                >
                                    <Fish class="h-4 w-4" />
                                </span>
                            </div>
                            <div
                                class="mt-4 pb-1 text-sm font-semibold text-slate-800"
                            >
                                {{ category.name }}
                            </div>
                        </Link>
                    </div>
                </div>
            </section>

            <section
                id="deals"
                class="mx-auto w-[min(1180px,calc(100%-32px))] pt-2 pb-5"
            >
                <div class="mb-5 flex items-center justify-center">
                    <h2 class="text-xl font-black text-[#124327]">
                        Popular Products
                    </h2>
                </div>

                <div
                    class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5"
                >
                    <article
                        v-for="product in products"
                        :key="product.id"
                        class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white p-3 shadow-[0_4px_16px_rgba(0,0,0,.08)]"
                    >
                        <span
                            class="absolute top-2 left-2 z-10 rounded-md bg-red-500 px-2 py-1 text-[10px] leading-tight font-black whitespace-pre-line text-white"
                            >{{
                                discountLabel(product).replace(' ', '\n')
                            }}</span
                        >

                        <Link
                            :href="productUrl(product)"
                            class="block overflow-hidden rounded-lg bg-slate-50"
                        >
                            <img
                                :src="
                                    product.thumbnail_url ??
                                    imageUrl(product.name)
                                "
                                :alt="product.name"
                                class="h-40 w-full object-cover transition duration-300 group-hover:scale-105"
                            />
                        </Link>

                        <div class="pt-3">
                            <Link
                                :href="productUrl(product)"
                                class="block min-h-10 text-xs leading-5 font-semibold text-slate-800 hover:text-[#176536]"
                            >
                                {{ product.name }}
                            </Link>
                            <p
                                class="mt-1 line-clamp-2 text-[10px] leading-4 text-slate-500"
                            >
                                {{
                                    product.short_description ||
                                    product.badge ||
                                    'Clean & Dressed'
                                }}
                            </p>
                            <p
                                v-if="netWeightLabel(product)"
                                class="mt-1 text-[10px] font-semibold text-[#176536]"
                            >
                                Net weight: {{ netWeightLabel(product) }}
                            </p>

                            <div class="mt-3 flex flex-wrap items-end gap-2">
                                <span
                                    v-if="showRegularPrice(product)"
                                    class="text-xs text-red-600 line-through"
                                    >{{
                                        displayPrice(product.regular_price)
                                    }}</span
                                >
                                <span
                                    class="text-base font-black text-[#176536]"
                                    >{{
                                        displayPrice(dealPrice(product))
                                    }}</span
                                >
                                <span class="pb-0.5 text-[10px] text-slate-500"
                                    >/{{ productUnit(product) }}</span
                                >
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    class="flex min-h-9 items-center justify-center gap-1.5 rounded-md border border-lime-500 px-2 text-[11px] font-bold text-lime-600 transition hover:bg-lime-500 hover:text-white disabled:cursor-not-allowed disabled:opacity-50"
                                    :class="
                                        product.is_in_cart
                                            ? 'border-[#176536] bg-[#176536] text-white hover:bg-[#0f4b27]'
                                            : ''
                                    "
                                    :disabled="
                                        product.stock_quantity < 1 &&
                                        !product.is_in_cart
                                    "
                                    @click="toggleCart(product)"
                                >
                                    <ShoppingCart class="h-3.5 w-3.5" />
                                    <span>{{
                                        product.is_in_cart
                                            ? 'View'
                                            : product.stock_quantity > 0
                                              ? 'Cart'
                                              : 'Out'
                                    }}</span>
                                </button>
                                <button
                                    type="button"
                                    class="flex min-h-9 items-center justify-center gap-1.5 rounded-md bg-[#176536] px-2 text-[11px] font-bold text-white transition hover:bg-[#0f4b27]"
                                >
                                    <ShoppingBag class="h-3.5 w-3.5" />
                                    <span>Buy</span>
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <section class="mx-auto w-[min(1180px,calc(100%-32px))] py-6">
                <div
                    class="overflow-hidden rounded-2xl bg-gradient-to-r from-[#eff9ea] via-white to-[#eef9e8] shadow-[0_8px_30px_rgba(0,0,0,.07)]"
                >
                    <div
                        class="grid items-center gap-6 px-5 py-6 lg:grid-cols-[1.1fr_2fr] lg:px-10"
                    >
                        <div class="flex items-center gap-5">
                            <div
                                class="grid h-24 w-24 shrink-0 place-items-center rounded-full bg-[#e0f5e5] text-[#176536]"
                            >
                                <Bike class="h-12 w-12" />
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-[#124327]">
                                    Freshness Delivered<br />to Your Doorstep
                                </h3>
                                <p class="mt-2 text-xs text-slate-600">
                                    Hygienic packing • On-time delivery • 100%
                                    satisfaction
                                </p>
                                <Link
                                    href="/shop"
                                    class="mt-4 inline-block rounded-md bg-lime-500 px-5 py-2.5 text-xs font-bold text-white hover:bg-lime-600"
                                    >Shop Now</Link
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div class="text-center">
                                <Leaf class="mx-auto h-8 w-8 text-[#176536]" />
                                <div class="mt-2 text-xs font-bold">
                                    100% Fresh<br />Sourced Daily
                                </div>
                            </div>
                            <div class="text-center">
                                <PackageCheck
                                    class="mx-auto h-8 w-8 text-[#176536]"
                                />
                                <div class="mt-2 text-xs font-bold">
                                    Hygienically<br />Packed
                                </div>
                            </div>
                            <div class="text-center">
                                <Truck class="mx-auto h-8 w-8 text-[#176536]" />
                                <div class="mt-2 text-xs font-bold">
                                    Fast & Reliable<br />Delivery
                                </div>
                            </div>
                            <div class="text-center">
                                <ShieldCheck
                                    class="mx-auto h-8 w-8 text-[#176536]"
                                />
                                <div class="mt-2 text-xs font-bold">
                                    Secure<br />Payments
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <SiteFooter />
    </div>
</template>
