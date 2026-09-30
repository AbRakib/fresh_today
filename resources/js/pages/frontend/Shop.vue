<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ChevronRight,
    Heart,
    Leaf,
    PackageCheck,
    ShieldCheck,
    ShoppingBag,
    ShoppingCart,
    Truck,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import SiteFooter from '@/components/site/SiteFooter.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';
import { useCurrency } from '@/composables/useCurrency';

type Product = {
    id: number;
    category_id: number | null;
    category_name: string | null;
    subcategory_id: number | null;
    subcategory_name: string | null;
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
    is_featured: number;
    is_wishlisted: boolean;
    is_in_cart: boolean;
};

type FrontendCategory = {
    id: number;
    name: string;
    icon_url: string | null;
};

const { money } = useCurrency();
const displayPrice = (value: string | null) => money(value ?? 0);

const priceOptions = [
    { value: '', label: 'Any Price', min: 0, max: Infinity },
    { value: 'under-500', label: `Under ${money(500)}`, min: 0, max: 500 },
    {
        value: '500-1000',
        label: `${money(500)} - ${money(1000)}`,
        min: 500,
        max: 1000,
    },
    {
        value: '1000-1500',
        label: `${money(1000)} - ${money(1500)}`,
        min: 1000,
        max: 1500,
    },
    {
        value: 'above-1500',
        label: `Above ${money(1500)}`,
        min: 1500,
        max: Infinity,
    },
];
const weightOptions = [
    { value: 'up-to-250', label: 'Up to 250g', min: 0, max: 250 },
    { value: '250-500', label: '250g - 500g', min: 250, max: 500 },
    { value: '500-1000', label: '500g - 1kg', min: 500, max: 1000 },
    { value: '1000-2000', label: '1kg - 2kg', min: 1000, max: 2000 },
    { value: 'above-2000', label: 'Above 2kg', min: 2000, max: Infinity },
];

const page = usePage<{
    frontend_products?: Product[];
    frontend_categories?: FrontendCategory[];
}>();
const products = computed(() => page.props.frontend_products ?? []);
const categories = computed(() => page.props.frontend_categories ?? []);
const selectedCategory = ref('');
const selectedPrice = ref('');
const selectedWeights = ref<string[]>([]);
const currentPage = ref(1);
const perPage = 8;

const categoryOptions = computed(() => {
    const counts = new Map<number, number>();
    let uncategorizedCount = 0;

    products.value.forEach((product) => {
        if (product.category_id) {
            counts.set(
                product.category_id,
                (counts.get(product.category_id) ?? 0) + 1,
            );

            return;
        }

        uncategorizedCount++;
    });

    const categoryItems = categories.value.map((category) => ({
        id: String(category.id),
        name: category.name,
        count: counts.get(category.id) ?? 0,
    }));

    if (uncategorizedCount > 0) {
        categoryItems.push({
            id: 'uncategorized',
            name: 'Uncategorized',
            count: uncategorizedCount,
        });
    }

    return categoryItems.sort((a, b) => a.name.localeCompare(b.name));
});

const selectedPriceOption = computed(
    () =>
        priceOptions.find((option) => option.value === selectedPrice.value) ??
        priceOptions[0],
);

const productWeightInGrams = (product: Product) => {
    const text =
        `${product.weight || product.gross_weight || ''} ${product.unit || ''}`
            .trim()
            .toLowerCase();
    const amount = Number.parseFloat(text.replace(/,/g, ''));

    if (!Number.isFinite(amount)) {
        return null;
    }

    return text.includes('kg') ? amount * 1000 : amount;
};

const productUrl = (product: Product) => `/product/${product.slug}`;
const toggleWishlist = (product: Product) => {
    const options = { preserveScroll: true, preserveState: true };

    if (product.is_wishlisted) {
        router.delete(`/wishlist/${product.id}`, options);

        return;
    }

    router.post(`/wishlist/${product.id}`, {}, options);
};
const toggleCart = (product: Product) => {
    const options = { preserveScroll: true, preserveState: true };

    if (product.is_in_cart) {
        router.visit('/cart');

        return;
    }

    router.post(`/cart/${product.id}`, {}, options);
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
const dealPrice = (product: Product) =>
    product.sale_price || product.regular_price;
const showRegularPrice = (product: Product) =>
    Boolean(product.sale_price) && product.sale_price !== product.regular_price;

const filteredProducts = computed(() => {
    const priceRange = selectedPriceOption.value;
    const selectedRanges = weightOptions.filter((option) =>
        selectedWeights.value.includes(option.value),
    );
    const result = products.value.filter((product) => {
        const price = Number(dealPrice(product));
        const weight = productWeightInGrams(product);
        const matchesCategory =
            !selectedCategory.value ||
            (selectedCategory.value === 'uncategorized'
                ? !product.category_id
                : String(product.category_id) === selectedCategory.value);
        const matchesPrice =
            price >= priceRange.min &&
            (priceRange.max === Infinity ? true : price < priceRange.max);
        const matchesWeight =
            selectedRanges.length === 0 ||
            (weight !== null &&
                selectedRanges.some(
                    (range) =>
                        weight >= range.min &&
                        (range.max === Infinity ? true : weight < range.max),
                ));

        return matchesCategory && matchesPrice && matchesWeight;
    });

    return result.sort((a, b) => b.is_featured - a.is_featured || b.id - a.id);
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredProducts.value.length / perPage)),
);
const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * perPage;

    return filteredProducts.value.slice(start, start + perPage);
});
const resetFilters = () => {
    selectedCategory.value = '';
    selectedPrice.value = '';
    selectedWeights.value = [];
};

watch(
    [selectedCategory, selectedPrice, selectedWeights],
    () => {
        currentPage.value = 1;
    },
    { deep: true },
);
const discountLabel = (product: Product) => {
    const discount = Number(product.discount_percentage ?? 0);

    if (discount > 0) {
        return `${Number.isInteger(discount) ? discount : discount.toFixed(1)}% OFF`;
    }

    return product.badge;
};
const productUnit = (product: Product) =>
    formatWeightLabel(product.weight || product.gross_weight, product.unit) ||
    'Per item';

const imageUrl = (text: string) =>
    `https://placehold.co/420x360/f5f7f4/23833f?text=${encodeURIComponent(text)}`;
</script>

<template>
    <Head title="Shop">
        <link rel="preconnect" href="https://placehold.co" />
    </Head>

    <div class="min-h-screen bg-[#f7faf7] text-[#213228]">
        <SiteHeader />

        <main class="mx-auto w-[min(1180px,calc(100%-32px))] py-2 pb-8">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-6">
                <div>
                    <div
                        class="mb-6 flex items-center gap-2 text-xs text-slate-500"
                    >
                        <Link href="/" class="hover:text-[#218a37]">Home</Link>
                        <ChevronRight class="h-3 w-3" />
                        <span>Shop</span>
                    </div>
                    <h1
                        class="text-4xl font-black tracking-tight text-[#1b2f24]"
                    >
                        Shop
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
                                class="flex cursor-pointer items-center justify-between gap-2"
                            >
                                <span class="flex items-center gap-2">
                                    <input
                                        v-model="selectedCategory"
                                        type="radio"
                                        value=""
                                        class="h-4 w-4 accent-lime-500"
                                    />
                                    All Categories
                                </span>
                                <span class="text-slate-500">{{
                                    products.length
                                }}</span>
                            </label>
                            <label
                                v-for="category in categoryOptions"
                                :key="category.id"
                                class="flex cursor-pointer items-center justify-between gap-2"
                            >
                                <span class="flex items-center gap-2">
                                    <input
                                        v-model="selectedCategory"
                                        type="radio"
                                        :value="category.id"
                                        class="h-4 w-4 accent-lime-500"
                                    />
                                    {{ category.name }}
                                </span>
                                <span class="text-slate-500">{{
                                    category.count
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
                                v-for="price in priceOptions.slice(1)"
                                :key="price.value"
                                type="button"
                                class="w-full rounded border px-3 py-2 text-left text-xs"
                                :class="
                                    selectedPrice === price.value
                                        ? 'border-lime-500 bg-lime-50 text-lime-700'
                                        : 'border-slate-200 hover:border-lime-400'
                                "
                                @click="
                                    selectedPrice =
                                        selectedPrice === price.value
                                            ? ''
                                            : price.value
                                "
                            >
                                {{ price.label }}
                            </button>
                        </div>
                    </section>

                    <section
                        class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <h2 class="mb-4 text-sm font-bold">Weight</h2>
                        <label
                            v-for="weight in weightOptions"
                            :key="weight.value"
                            class="mb-3 flex cursor-pointer items-center gap-2 text-xs"
                        >
                            <input
                                v-model="selectedWeights"
                                type="checkbox"
                                :value="weight.value"
                                class="h-4 w-4 rounded accent-lime-500"
                            />
                            {{ weight.label }}
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
                        class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4"
                    >
                        <article
                            v-for="product in paginatedProducts"
                            :key="product.id"
                            class="relative flex h-full flex-col rounded-lg border border-slate-200 bg-white p-2 shadow-sm"
                        >
                            <span
                                v-if="discountLabel(product)"
                                class="absolute top-3 left-3 z-10 rounded bg-red-500 px-2 py-1 text-[10px] font-black text-white"
                            >
                                {{ discountLabel(product) }}
                            </span>
                            <button
                                class="absolute top-3 right-3 z-10 grid h-7 w-7 place-items-center rounded-full bg-white text-slate-400 shadow"
                                :class="
                                    product.is_wishlisted
                                        ? 'border border-red-200 bg-red-50 text-red-500'
                                        : 'hover:text-lime-600'
                                "
                                :aria-label="
                                    product.is_wishlisted
                                        ? 'Already in wishlist'
                                        : 'Add to wishlist'
                                "
                                @click="toggleWishlist(product)"
                            >
                                <Heart
                                    class="h-4 w-4"
                                    :class="
                                        product.is_wishlisted
                                            ? 'fill-red-500 text-red-500'
                                            : ''
                                    "
                                    :fill="
                                        product.is_wishlisted
                                            ? 'currentColor'
                                            : 'none'
                                    "
                                />
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
                            <div class="flex flex-1 flex-col p-2">
                                <Link
                                    :href="productUrl(product)"
                                    class="block text-sm font-bold text-slate-800 hover:text-[#218a37]"
                                >
                                    {{ product.name }}
                                </Link>
                                <p
                                    v-if="product.short_description"
                                    class="mt-1 line-clamp-2 text-xs leading-4 text-slate-500"
                                >
                                    {{ product.short_description }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ productUnit(product) }}
                                </p>
                                <p
                                    v-if="netWeightLabel(product)"
                                    class="mt-1 text-xs font-semibold text-[#218a37]"
                                >
                                    Net weight: {{ netWeightLabel(product) }}
                                </p>
                                <div class="mt-2 flex items-end gap-2">
                                    <span
                                        v-if="showRegularPrice(product)"
                                        class="text-xs text-red-600 line-through"
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
                                <div
                                    class="mt-auto grid grid-cols-2 gap-2 pt-3"
                                >
                                    <button
                                        type="button"
                                        class="flex min-h-9 items-center justify-center gap-1.5 rounded border border-lime-500 px-2 text-[11px] font-bold text-lime-600 transition hover:bg-lime-500 hover:text-white disabled:cursor-not-allowed disabled:opacity-50"
                                        :class="
                                            product.is_in_cart
                                                ? 'border-[#218a37] bg-[#218a37] text-white hover:bg-[#176536]'
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
                                        class="flex min-h-9 items-center justify-center gap-1.5 rounded bg-[#218a37] px-2 text-[11px] font-bold text-white transition hover:bg-[#176536]"
                                    >
                                        <ShoppingBag class="h-3.5 w-3.5" />
                                        <span>Buy</span>
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div
                        v-if="filteredProducts.length === 0"
                        class="rounded-lg border border-dashed border-slate-300 bg-white px-6 py-14 text-center"
                    >
                        <p class="font-semibold text-slate-700">
                            No products found
                        </p>
                        <button
                            type="button"
                            class="mt-3 text-sm font-semibold text-lime-600 hover:text-lime-700"
                            @click="resetFilters"
                        >
                            Clear filters
                        </button>
                    </div>

                    <div
                        v-if="filteredProducts.length > 0 && totalPages > 1"
                        class="mt-8 flex justify-center gap-2"
                    >
                        <button
                            type="button"
                            class="grid h-9 w-9 place-items-center rounded border border-slate-200 bg-white disabled:cursor-not-allowed disabled:opacity-40"
                            :disabled="currentPage === 1"
                            aria-label="Previous page"
                            @click="currentPage--"
                        >
                            <ChevronRight class="h-4 w-4 rotate-180" />
                        </button>
                        <button
                            v-for="pageNumber in totalPages"
                            :key="pageNumber"
                            type="button"
                            class="grid h-9 w-9 place-items-center rounded border text-sm"
                            :class="
                                currentPage === pageNumber
                                    ? 'border-lime-500 bg-lime-500 font-bold text-white'
                                    : 'border-slate-200 bg-white'
                            "
                            @click="currentPage = pageNumber"
                        >
                            {{ pageNumber }}
                        </button>
                        <button
                            type="button"
                            class="grid h-9 w-9 place-items-center rounded border border-slate-200 bg-white disabled:cursor-not-allowed disabled:opacity-40"
                            :disabled="currentPage === totalPages"
                            aria-label="Next page"
                            @click="currentPage++"
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
