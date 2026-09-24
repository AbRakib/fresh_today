<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Bike,
    ChevronLeft,
    ChevronRight,
    Fish,
    Leaf,
    PackageCheck,
    ShieldCheck,
    ShoppingCart,
    Truck,
} from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
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
};

type FrontendCategory = {
    id: number;
    name: string;
    icon_url: string | null;
};

const page = usePage<{
    frontend_categories?: FrontendCategory[];
    frontend_products?: Product[];
}>();
const categories = computed(() => page.props.frontend_categories ?? []);
const products = computed(() => page.props.frontend_products ?? []);

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
const productUrl = (product: Product) => `/product/${product.slug}`;

const netWeightLabel = (product: Product) =>
    [product.weight, product.unit].filter(Boolean).join(' ');

const productUnit = (product: Product) => {
    const amount = product.weight || product.gross_weight;

    return (
        [amount, product.unit].filter(Boolean).join(' ') ||
        product.unit ||
        'item'
    );
};

const totalSeconds = ref(10 * 3600 + 45 * 60 + 32);
let countdownTimer: number | undefined;

const countdown = computed(() => {
    const hours = String(Math.floor(totalSeconds.value / 3600)).padStart(
        2,
        '0',
    );
    const minutes = String(
        Math.floor((totalSeconds.value % 3600) / 60),
    ).padStart(2, '0');
    const seconds = String(totalSeconds.value % 60).padStart(2, '0');

    return `${hours} : ${minutes} : ${seconds}`;
});

const imageUrl = (text: string, size = '500x360') =>
    `https://placehold.co/${size}/f7f8f5/2f7d45?text=${encodeURIComponent(text)}`;

onMounted(() => {
    countdownTimer = window.setInterval(() => {
        totalSeconds.value = Math.max(0, totalSeconds.value - 1);
    }, 1000);
});

onBeforeUnmount(() => {
    if (countdownTimer) {
        window.clearInterval(countdownTimer);
    }
});
</script>

<template>
    <Head title="Fresh Today">
        <link rel="preconnect" href="https://placehold.co" />
    </Head>

    <div class="min-h-screen bg-white font-sans text-slate-800">
        <SiteHeader />

        <main>
            <section class="mx-auto w-[min(1180px,calc(100%-32px))] pt-5">
                <div
                    class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#f5fae8] via-[#fffcef] to-[#f4fbeb] shadow-[0_8px_30px_rgba(0,0,0,.07)]"
                >
                    <div class="grid min-h-[450px] lg:grid-cols-[1fr_1.3fr]">
                        <div
                            class="relative z-10 flex flex-col justify-center px-7 py-12 sm:px-12"
                        >
                            <span class="mb-3 text-2xl text-[#176536] italic"
                                >Fresh & Nutritious</span
                            >
                            <h1
                                class="text-4xl leading-none font-black text-wrap text-[#15512e] uppercase sm:text-5xl"
                            >
                                Family Fish Protein
                            </h1>
                            <div
                                class="mt-1 text-5xl leading-none font-black text-wrap text-[#176536] italic"
                            >
                                Combo 1
                            </div>

                            <div
                                class="mt-7 flex flex-wrap gap-x-5 gap-y-3 text-xs font-medium text-slate-700"
                            >
                                <span class="flex items-center gap-2"
                                    ><BadgeCheck
                                        class="h-5 w-5 text-[#207f42]"
                                    />
                                    Premium Quality</span
                                >
                                <span class="flex items-center gap-2"
                                    ><PackageCheck
                                        class="h-5 w-5 text-[#207f42]"
                                    />
                                    Hygienically Packed</span
                                >
                                <span class="flex items-center gap-2"
                                    ><Leaf class="h-5 w-5 text-[#207f42]" />
                                    Direct From Source</span
                                >
                            </div>

                            <a
                                href="#deals"
                                class="mt-7 inline-flex w-fit items-center rounded-md bg-lime-500 px-6 py-3 text-sm font-bold text-white shadow hover:bg-lime-600"
                                >Shop Combo</a
                            >
                        </div>

                        <div class="relative min-h-[300px]">
                            <div
                                class="absolute top-8 right-8 z-20 rounded-2xl border-2 border-red-400 bg-white/90 p-3 text-center shadow"
                            >
                                <div
                                    class="rounded-xl bg-red-500 px-3 py-1 text-lg font-black text-white"
                                >
                                    GET 6% OFF
                                </div>
                                <div
                                    class="mt-1 text-xs font-semibold text-slate-700"
                                >
                                    FINAL PRICE
                                </div>
                                <div class="text-2xl font-black text-[#15512e]">
                                    {{ money(2077) }}
                                </div>
                            </div>

                            <img
                                :src="
                                    imageUrl(
                                        'Fresh Fish Combo Presentation',
                                        '1100x650',
                                    )
                                "
                                alt="Fish combo"
                                class="h-full w-full object-cover"
                            />

                            <div
                                class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-white/95 via-white/40 to-transparent p-5"
                            >
                                <div
                                    class="grid grid-cols-2 gap-2 text-center text-[10px] sm:grid-cols-5 sm:text-xs"
                                >
                                    <div>
                                        <div class="font-semibold">
                                            Mixed Fish
                                        </div>
                                        <div class="font-black text-[#15512e]">
                                            {{ money(335) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-semibold">
                                            Koi Fish
                                        </div>
                                        <div class="font-black text-[#15512e]">
                                            {{ money(444) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-semibold">
                                            Deshi Shol
                                        </div>
                                        <div class="font-black text-[#15512e]">
                                            {{ money(480) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-semibold">
                                            Small Prawn
                                        </div>
                                        <div class="font-black text-[#15512e]">
                                            {{ money(427) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-semibold">
                                            River Baila
                                        </div>
                                        <div class="font-black text-[#15512e]">
                                            {{ money(524) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button
                        class="absolute top-1/2 left-3 grid h-9 w-9 -translate-y-1/2 place-items-center rounded-full bg-white text-slate-700 shadow"
                        aria-label="Previous slide"
                    >
                        <ChevronLeft class="h-5 w-5" />
                    </button>
                    <button
                        class="absolute top-1/2 right-3 grid h-9 w-9 -translate-y-1/2 place-items-center rounded-full bg-white text-slate-700 shadow"
                        aria-label="Next slide"
                    >
                        <ChevronRight class="h-5 w-5" />
                    </button>
                </div>

                <div class="mt-3 flex justify-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-[#207f42]"></span>
                    <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                    <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                    <span class="h-2 w-2 rounded-full bg-slate-300"></span>
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
                            <div
                                class="relative overflow-hidden rounded-lg bg-slate-50"
                            >
                                <img
                                    :src="imageUrl(category.name, '500x320')"
                                    :alt="category.name"
                                    class="h-32 w-full object-cover transition duration-300 group-hover:scale-105"
                                />
                                <span
                                    class="absolute bottom-2 left-1/2 grid h-9 w-9 -translate-x-1/2 translate-y-1/2 place-items-center rounded-full border border-[#97d6a8] bg-white text-[#176536] shadow"
                                >
                                    <img
                                        v-if="category.icon_url"
                                        :src="category.icon_url"
                                        :alt="category.name"
                                        class="h-4 w-4 object-contain"
                                    />
                                    <Fish v-else class="h-4 w-4" />
                                </span>
                            </div>
                            <div
                                class="mt-6 pb-1 text-sm font-semibold text-slate-800"
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
                <div
                    class="mb-5 flex flex-wrap items-center justify-center gap-4"
                >
                    <h2 class="text-xl font-black text-[#124327]">
                        Deals of the Day
                    </h2>
                    <div
                        class="rounded-full border border-[#97d6a8] bg-[#f2fbf4] px-3 py-1 text-xs font-semibold text-[#15512e]"
                    >
                        Offer ends in
                        <span class="ml-2 font-mono text-sm font-black">{{
                            countdown
                        }}</span>
                    </div>
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
                            <p class="mt-1 text-[10px] text-slate-500">
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
                                    class="text-xs text-slate-400 line-through"
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

                            <button
                                class="mt-3 flex w-full items-center justify-center gap-2 rounded-md border border-lime-500 py-2 text-xs font-bold text-lime-600 transition hover:bg-lime-500 hover:text-white"
                            >
                                Add to Cart
                                <ShoppingCart class="h-3.5 w-3.5" />
                            </button>
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
                                <a
                                    href="#deals"
                                    class="mt-4 inline-block rounded-md bg-lime-500 px-5 py-2.5 text-xs font-bold text-white hover:bg-lime-600"
                                    >Shop Now</a
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
