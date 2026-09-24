<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Check,
    ChevronDown,
    ChevronRight,
    Fish,
    Heart,
    Leaf,
    PackageCheck,
    ShieldCheck,
    ShoppingCart,
    SlidersHorizontal,
    Truck,
} from '@lucide/vue';
import { computed } from 'vue';
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

const filters = [
    ['All Fish & Seafood', ''],
    ['Fresh River Fish', '42'],
    ['Hilsha & Seasonal', '18'],
    ['Prawn & Shrimp', '20'],
    ['Crab & Shellfish', '14'],
    ['Dry Fish', '16'],
    ['Fish Steak & Fillet', '22'],
    ['Ready to Cook', '10'],
];

const { money } = useCurrency();
const displayPrice = (value: string | null) => money(value ?? 0);

const prices = [
    `Under ${money(500)}`,
    `${money(500)} - ${money(1000)}`,
    `${money(1000)} - ${money(1500)}`,
    `Above ${money(1500)}`,
];
const weights = [
    'Up to 250g',
    '250g - 500g',
    '500g - 1kg',
    '1kg - 2kg',
    'Above 2kg',
];

const page = usePage<{ frontend_products?: Product[] }>();
const products = computed(() => page.props.frontend_products ?? []);

const productUrl = (product: Product) => `/product/${product.slug}`;
const dealPrice = (product: Product) =>
    product.sale_price || product.regular_price;
const showRegularPrice = (product: Product) =>
    Boolean(product.sale_price) && product.sale_price !== product.regular_price;
const discountLabel = (product: Product) => {
    const discount = Number(product.discount_percentage ?? 0);

    if (discount > 0) {
        return `${Number.isInteger(discount) ? discount : discount.toFixed(1)}% OFF`;
    }

    return product.badge;
};
const productUnit = (product: Product) =>
    [product.weight || product.gross_weight, product.unit]
        .filter(Boolean)
        .join(' ') || 'Per item';

const imageUrl = (text: string) =>
    `https://placehold.co/420x360/f5f7f4/23833f?text=${encodeURIComponent(text)}`;
</script>

<template>
    <Head title="Fresh Fish & Seafood">
        <link rel="preconnect" href="https://placehold.co" />
    </Head>

    <div class="min-h-screen bg-[#f7faf7] text-[#213228]">
        <SiteHeader />

        <main class="mx-auto w-[min(1180px,calc(100%-32px))] py-8">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-6">
                <div>
                    <div
                        class="mb-6 flex items-center gap-2 text-xs text-slate-500"
                    >
                        <Link href="/" class="hover:text-[#218a37]">Home</Link>
                        <ChevronRight class="h-3 w-3" />
                        <span>Fresh Fish</span>
                    </div>
                    <h1
                        class="text-4xl font-black tracking-tight text-[#1b2f24]"
                    >
                        Fresh Fish & Seafood
                    </h1>
                    <p class="mt-3 text-sm text-slate-600">
                        100% fresh catch from trusted sources across Bangladesh.
                    </p>
                </div>

                <div
                    class="grid grid-cols-2 gap-4 rounded-xl bg-[#edf7ef] px-5 py-4 text-sm"
                >
                    <div class="flex items-center gap-3">
                        <Truck class="h-8 w-8 text-[#218a37]" />
                        <span
                            ><strong class="block text-[#218a37]"
                                >Fresh Delivery</strong
                            >On time, every time</span
                        >
                    </div>
                    <div class="flex items-center gap-3">
                        <ShieldCheck class="h-8 w-8 text-[#218a37]" />
                        <span
                            ><strong class="block text-[#218a37]"
                                >100% Quality</strong
                            >Premium & trusted</span
                        >
                    </div>
                </div>
            </div>

            <div class="grid gap-5 lg:grid-cols-[200px_1fr]">
                <aside class="space-y-4">
                    <div
                        class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <button
                            class="flex w-full items-center justify-center gap-2 rounded border border-slate-100 px-4 py-3 text-sm font-semibold"
                        >
                            <SlidersHorizontal class="h-4 w-4" /> All Filters
                        </button>
                    </div>

                    <section
                        class="rounded-lg border border-slate-200 bg-white shadow-sm"
                    >
                        <h2
                            class="border-b border-slate-100 px-4 py-3 text-sm font-bold"
                        >
                            Categories
                        </h2>
                        <div class="space-y-3 p-4 text-xs">
                            <label
                                v-for="(filter, index) in filters"
                                :key="filter[0]"
                                class="flex items-center justify-between gap-2"
                            >
                                <span class="flex items-center gap-2">
                                    <span
                                        class="grid h-4 w-4 place-items-center rounded-full border"
                                        :class="
                                            index === 0
                                                ? 'border-lime-500 bg-lime-500'
                                                : 'border-slate-300'
                                        "
                                    >
                                        <Check
                                            v-if="index === 0"
                                            class="h-3 w-3 text-white"
                                        />
                                    </span>
                                    {{ filter[0] }}
                                </span>
                                <span class="text-slate-500">{{
                                    filter[1]
                                }}</span>
                            </label>
                        </div>
                    </section>

                    <section
                        class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <h2 class="mb-4 text-sm font-bold">
                            Price Range ({{
                                money(0).replace(/[\d\s.,]/g, '')
                            }})
                        </h2>
                        <div class="h-1.5 rounded-full bg-lime-500"></div>
                        <div
                            class="mt-3 flex justify-between text-xs text-slate-500"
                        >
                            <span>{{ money(0) }}</span
                            ><span>{{ money(2500) }}</span>
                        </div>
                        <div class="mt-4 space-y-2">
                            <button
                                v-for="price in prices"
                                :key="price"
                                class="w-full rounded border border-slate-200 px-3 py-2 text-left text-xs"
                            >
                                {{ price }}
                            </button>
                        </div>
                    </section>

                    <section
                        class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <h2 class="mb-4 text-sm font-bold">Weight</h2>
                        <label
                            v-for="weight in weights"
                            :key="weight"
                            class="mb-3 flex items-center gap-2 text-xs"
                        >
                            <span
                                class="h-4 w-4 rounded border border-slate-300"
                            ></span
                            >{{ weight }}
                        </label>
                    </section>

                    <section
                        class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <h2 class="mb-4 text-sm font-bold">Availability</h2>
                        <label class="flex items-center gap-2 text-xs">
                            <span
                                class="grid h-4 w-4 place-items-center rounded bg-lime-500"
                                ><Check class="h-3 w-3 text-white"
                            /></span>
                            In Stock Only
                        </label>
                    </section>

                    <section
                        class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <h2 class="mb-4 text-sm font-bold">Offers</h2>
                        <label class="flex items-center gap-2 text-xs">
                            <span
                                class="h-4 w-4 rounded border border-slate-300"
                            ></span>
                            On Sale
                        </label>
                    </section>

                    <section
                        class="rounded-lg border border-green-100 bg-[#f1fbf0] p-5 text-center shadow-sm"
                    >
                        <img
                            :src="imageUrl('Need Help')"
                            alt=""
                            class="mx-auto mb-3 h-28 w-full rounded-lg object-cover"
                        />
                        <h2 class="text-lg font-black text-[#1f7b33]">
                            Need Help?
                        </h2>
                        <p class="mt-2 text-xs text-slate-600">
                            We're here to help you choose the best fish.
                        </p>
                        <button
                            class="mt-4 w-full rounded bg-lime-500 py-2 text-xs font-bold text-white hover:bg-lime-600"
                        >
                            Chat with us
                        </button>
                    </section>
                </aside>

                <section>
                    <div
                        class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <div class="flex flex-wrap gap-3">
                            <button
                                class="flex items-center gap-8 rounded border border-slate-200 px-4 py-2 text-xs"
                            >
                                <span
                                    ><span class="block text-slate-500"
                                        >Category</span
                                    >All Categories</span
                                >
                                <ChevronDown class="h-4 w-4" />
                            </button>
                            <button
                                class="flex items-center gap-8 rounded border border-slate-200 px-4 py-2 text-xs"
                            >
                                <span
                                    ><span class="block text-slate-500"
                                        >Price</span
                                    >Any Price</span
                                >
                                <ChevronDown class="h-4 w-4" />
                            </button>
                            <label class="flex items-center gap-3 text-xs">
                                <span
                                    class="flex h-6 w-11 items-center rounded-full bg-lime-500 p-1"
                                >
                                    <span
                                        class="h-4 w-4 rounded-full bg-white"
                                    ></span>
                                </span>
                                In Stock Only
                            </label>
                        </div>
                        <button
                            class="flex items-center gap-4 rounded border border-slate-200 px-4 py-2 text-xs"
                        >
                            <span
                                ><span class="text-slate-500">Sort by</span>
                                Popularity</span
                            >
                            <ChevronDown class="h-4 w-4" />
                        </button>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4"
                    >
                        <article
                            v-for="product in products"
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
                                class="absolute top-3 right-3 z-10 grid h-7 w-7 place-items-center rounded-full bg-white text-slate-400 shadow"
                            >
                                <Heart class="h-4 w-4" />
                            </button>
                            <Link :href="productUrl(product)" class="block">
                                <img
                                    :src="
                                        product.thumbnail_url ??
                                        imageUrl(product.name)
                                    "
                                    :alt="product.name"
                                    class="h-44 w-full rounded-md object-cover"
                                />
                            </Link>
                            <div class="p-2">
                                <Link
                                    :href="productUrl(product)"
                                    class="block text-sm font-bold text-slate-800 hover:text-[#218a37]"
                                >
                                    {{ product.name }}
                                </Link>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ productUnit(product) }}
                                </p>
                                <div class="mt-2 flex items-end gap-2">
                                    <span
                                        v-if="showRegularPrice(product)"
                                        class="text-xs text-slate-400 line-through"
                                        >{{
                                            displayPrice(product.regular_price)
                                        }}</span
                                    >
                                    <span
                                        class="text-lg font-black text-[#218a37]"
                                        >{{
                                            displayPrice(dealPrice(product))
                                        }}</span
                                    >
                                </div>
                                <button
                                    class="mt-3 flex w-full items-center justify-center gap-2 rounded border border-lime-500 py-2 text-xs font-bold text-lime-600 hover:bg-lime-500 hover:text-white"
                                >
                                    <ShoppingCart class="h-4 w-4" /> Add to cart
                                </button>
                            </div>
                        </article>
                    </div>

                    <div class="mt-8 flex justify-center gap-2">
                        <button
                            class="grid h-9 w-9 place-items-center rounded border border-slate-200 bg-white"
                        >
                            <ChevronRight class="h-4 w-4 rotate-180" />
                        </button>
                        <button
                            class="grid h-9 w-9 place-items-center rounded bg-lime-500 text-sm font-bold text-white"
                        >
                            1
                        </button>
                        <button
                            v-for="page in ['2', '3', '4']"
                            :key="page"
                            class="grid h-9 w-9 place-items-center rounded border border-slate-200 bg-white text-sm"
                        >
                            {{ page }}
                        </button>
                        <span
                            class="grid h-9 place-items-center px-2 text-slate-500"
                            >...</span
                        >
                        <button
                            class="grid h-9 w-9 place-items-center rounded border border-slate-200 bg-white text-sm"
                        >
                            10
                        </button>
                        <button
                            class="grid h-9 w-9 place-items-center rounded border border-slate-200 bg-white"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </button>
                    </div>
                </section>
            </div>
        </main>

        <section
            class="mx-auto mb-10 w-[min(1180px,calc(100%-32px))] rounded-xl bg-white p-6 shadow-lg"
        >
            <div class="grid gap-5 md:grid-cols-4">
                <div class="flex items-center gap-4">
                    <Leaf class="h-9 w-9 text-[#218a37]" /><span
                        ><strong class="block">Fresh & Trusted</strong
                        ><small
                            >Sourced daily from trusted suppliers</small
                        ></span
                    >
                </div>
                <div class="flex items-center gap-4">
                    <PackageCheck class="h-9 w-9 text-[#218a37]" /><span
                        ><strong class="block">Hygienic Packaging</strong
                        ><small>Cleaned & packed with care</small></span
                    >
                </div>
                <div class="flex items-center gap-4">
                    <Truck class="h-9 w-9 text-[#218a37]" /><span
                        ><strong class="block">Fast Home Delivery</strong
                        ><small>On time across Dhaka & beyond</small></span
                    >
                </div>
                <div class="flex items-center gap-4">
                    <ShieldCheck class="h-9 w-9 text-[#218a37]" /><span
                        ><strong class="block">100% Satisfaction</strong
                        ><small>Easy returns & best support</small></span
                    >
                </div>
            </div>
        </section>

        <SiteFooter />
    </div>
</template>
