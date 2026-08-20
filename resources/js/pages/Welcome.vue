<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Beef,
    Bike,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Drumstick,
    Fish,
    Leaf,
    List,
    Mail,
    PackageCheck,
    Phone,
    Salad,
    Search,
    ShieldCheck,
    ShoppingCart,
    Truck,
    UserRound,
} from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { dashboard, login, register } from '@/routes';

type Product = {
    name: string;
    unit: string;
    old: string;
    price: string;
    discount: string;
};

const categories = [
    { name: 'Fresh Water Fish', label: 'fresh water fish', icon: Fish },
    { name: 'Shell Fish', label: 'shell fish', icon: Fish },
    { name: 'Seafood', label: 'seafood', icon: Fish },
    { name: 'Steaks & Fillets', label: 'steaks', icon: Fish },
    { name: 'Chicken & Duck', label: 'chicken', icon: Drumstick },
    { name: 'Beef & Mutton', label: 'meat', icon: Beef },
    { name: 'Combo Pack', label: 'combo', icon: Fish },
    { name: 'Dried Fish', label: 'dried fish', icon: Fish },
    { name: 'Paste Spice', label: 'spices', icon: Salad },
];

const categoryLinks = [
    'River & Fresh Water Fish',
    'Shell Fish',
    'Seafood',
    'Steaks & Fillets',
    'Chicken & Duck',
    'Beef & Mutton',
    'Combo Pack',
    'Dried Fish',
    'Paste Spice',
];

const products: Product[] = [
    {
        name: 'Imported Frozen Dory Fillets',
        unit: '1 kg',
        old: '৳640',
        price: '৳580',
        discount: '12% OFF',
    },
    {
        name: 'Deshi Magur Fish',
        unit: '500g',
        old: '৳492',
        price: '৳450',
        discount: '8% OFF',
    },
    {
        name: 'River Baila Fish',
        unit: '500g',
        old: '৳590',
        price: '৳524',
        discount: '11% OFF',
    },
    {
        name: 'Biler Deshi Shing Fish',
        unit: '500g',
        old: '৳599',
        price: '৳444',
        discount: '10% OFF',
    },
    {
        name: 'River Gulsha Tengra Fish',
        unit: '500g',
        old: '৳538',
        price: '৳490',
        discount: '9% OFF',
    },
    {
        name: 'Datina Koral Fish',
        unit: '1 kg',
        old: '৳770',
        price: '৳720',
        discount: '7% OFF',
    },
    {
        name: 'River Boal Fish',
        unit: '1 kg',
        old: '৳684',
        price: '৳630',
        discount: '9% OFF',
    },
    {
        name: 'Sea Lal Poa Fish',
        unit: '1 kg',
        old: '৳744',
        price: '৳670',
        discount: '10% OFF',
    },
    {
        name: 'Bagda Shrimp Whole',
        unit: '500g',
        old: '৳990',
        price: '৳930',
        discount: '6% OFF',
    },
    {
        name: 'Panchmishali Fish',
        unit: '1 kg',
        old: '৳649',
        price: '৳575',
        discount: '10% OFF',
    },
    {
        name: 'Premium Rui Fish',
        unit: '1 kg',
        old: '৳720',
        price: '৳660',
        discount: '8% OFF',
    },
    {
        name: 'Fresh Prawn Medium',
        unit: '500g',
        old: '৳880',
        price: '৳799',
        discount: '9% OFF',
    },
    {
        name: 'Tilapia Clean & Dressed',
        unit: '1 kg',
        old: '৳430',
        price: '৳399',
        discount: '7% OFF',
    },
    {
        name: 'Katla Fish Steak',
        unit: '500g',
        old: '৳510',
        price: '৳459',
        discount: '10% OFF',
    },
    {
        name: 'Fresh Salmon Cut',
        unit: '500g',
        old: '৳1450',
        price: '৳1320',
        discount: '9% OFF',
    },
];

const visibleProducts = ref(10);
const totalSeconds = ref(10 * 3600 + 45 * 60 + 32);
let countdownTimer: number | undefined;

const visibleProductList = computed(() =>
    products.slice(0, visibleProducts.value),
);
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

const loadMoreProducts = () => {
    visibleProducts.value += 5;
};

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
        <div class="bg-[#15512e] text-xs text-white">
            <div
                class="mx-auto flex h-9 w-[min(1180px,calc(100%-32px))] items-center justify-between gap-4"
            >
                <div class="flex items-center gap-5">
                    <a
                        href="tel:09617551122"
                        class="flex items-center gap-2 hover:text-[#e0f5e5]"
                    >
                        <Phone class="h-3.5 w-3.5" />
                        <span>09617 551122</span>
                    </a>
                    <a
                        href="mailto:support@freshtodaybd.com"
                        class="hidden items-center gap-2 hover:text-[#e0f5e5] sm:flex"
                    >
                        <Mail class="h-3.5 w-3.5" />
                        <span>support@freshtodaybd.com</span>
                    </a>
                </div>

                <div class="flex items-center gap-4">
                    <button class="flex items-center gap-1">
                        EN <ChevronDown class="h-3 w-3" />
                    </button>
                    <button class="flex items-center gap-1">
                        BDT <ChevronDown class="h-3 w-3" />
                    </button>
                    <a href="#" class="hidden md:block">Campaigns</a>
                    <a href="#" class="hidden md:block">Wishlist (0)</a>
                </div>
            </div>
        </div>

        <header class="border-b border-slate-100 bg-white">
            <div
                class="mx-auto flex w-[min(1180px,calc(100%-32px))] items-center gap-4 py-4"
            >
                <Link href="/" class="shrink-0">
                    <div class="flex items-center gap-2">
                        <div
                            class="grid h-12 w-12 place-items-center rounded-full bg-[#176536] text-2xl font-black text-white"
                        >
                            F
                        </div>
                        <div class="hidden leading-tight sm:block">
                            <div class="text-xl font-black text-[#15512e]">
                                fresh
                            </div>
                            <div class="-mt-1 text-lg font-bold text-red-500">
                                Today
                            </div>
                        </div>
                    </div>
                </Link>

                <div class="relative mx-auto max-w-2xl flex-1">
                    <input
                        type="text"
                        placeholder="Search for fish, meat & more..."
                        class="h-12 w-full rounded-md border border-slate-200 bg-white px-4 pr-14 text-sm transition outline-none focus:border-[#319d57] focus:ring-2 focus:ring-[#e0f5e5]"
                    />
                    <button
                        class="absolute top-0 right-0 grid h-12 w-12 place-items-center rounded-r-md bg-[#176536] text-white hover:bg-[#15512e]"
                        aria-label="Search"
                    >
                        <Search class="h-5 w-5" />
                    </button>
                </div>

                <nav class="hidden items-center gap-7 lg:flex">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="flex items-center gap-2 text-sm hover:text-[#176536]"
                    >
                        <UserRound class="h-6 w-6 text-[#176536]" />
                        <span>Dashboard</span>
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="flex items-center gap-2 text-sm hover:text-[#176536]"
                        >
                            <UserRound class="h-6 w-6 text-[#176536]" />
                            <span>Account</span>
                        </Link>
                        <Link
                            :href="register()"
                            class="text-sm font-semibold text-[#176536] hover:text-[#15512e]"
                            >Register</Link
                        >
                    </template>
                    <a
                        href="#"
                        class="relative flex items-center gap-2 text-sm hover:text-[#176536]"
                    >
                        <ShoppingCart class="h-6 w-6 text-[#176536]" />
                        <span>Cart</span>
                        <span
                            class="absolute -top-3 -right-2 grid h-5 min-w-5 place-items-center rounded-full bg-[#176536] px-1 text-[10px] font-bold text-white"
                            >0</span
                        >
                    </a>
                </nav>
            </div>

            <div class="border-t border-slate-100">
                <div
                    class="mx-auto flex w-[min(1180px,calc(100%-32px))] [scrollbar-width:none] gap-6 overflow-x-auto py-3 text-xs font-medium text-slate-700 [&::-webkit-scrollbar]:hidden"
                >
                    <a
                        href="#"
                        class="flex shrink-0 items-center gap-1.5 font-semibold text-[#15512e]"
                    >
                        <List class="h-4 w-4" />
                        All Categories
                    </a>
                    <Link
                        v-for="link in categoryLinks"
                        :key="link"
                        href="/fresh-fish"
                        class="shrink-0 hover:text-[#176536]"
                    >
                        {{ link }}
                    </Link>
                </div>
            </div>
        </header>

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
                                class="mt-7 inline-flex w-fit items-center rounded-md bg-[#176536] px-6 py-3 text-sm font-bold text-white shadow hover:bg-[#15512e]"
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
                                    ৳2,077
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
                                            ৳335
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-semibold">
                                            Koi Fish
                                        </div>
                                        <div class="font-black text-[#15512e]">
                                            ৳444
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-semibold">
                                            Deshi Shol
                                        </div>
                                        <div class="font-black text-[#15512e]">
                                            ৳480
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-semibold">
                                            Small Prawn
                                        </div>
                                        <div class="font-black text-[#15512e]">
                                            ৳427
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-semibold">
                                            River Baila
                                        </div>
                                        <div class="font-black text-[#15512e]">
                                            ৳524
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
                            :key="category.name"
                            href="/fresh-fish"
                            class="group rounded-xl border border-slate-200 bg-white p-3 text-center shadow-[0_4px_16px_rgba(0,0,0,.08)] transition hover:-translate-y-1 hover:border-[#97d6a8]"
                        >
                            <div
                                class="relative overflow-hidden rounded-lg bg-slate-50"
                            >
                                <img
                                    :src="imageUrl(category.label, '500x320')"
                                    :alt="category.name"
                                    class="h-32 w-full object-cover transition duration-300 group-hover:scale-105"
                                />
                                <span
                                    class="absolute bottom-2 left-1/2 grid h-9 w-9 -translate-x-1/2 translate-y-1/2 place-items-center rounded-full border border-[#97d6a8] bg-white text-[#176536] shadow"
                                >
                                    <component
                                        :is="category.icon"
                                        class="h-4 w-4"
                                    />
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
                        v-for="(product, index) in visibleProductList"
                        :key="product.name"
                        class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white p-3 shadow-[0_4px_16px_rgba(0,0,0,.08)]"
                    >
                        <span
                            class="absolute top-2 left-2 z-10 rounded-md bg-red-500 px-2 py-1 text-[10px] leading-tight font-black whitespace-pre-line text-white"
                            >{{ product.discount.replace(' ', '\n') }}</span
                        >

                        <div class="overflow-hidden rounded-lg bg-slate-50">
                            <img
                                :src="imageUrl(`Fresh Fish ${index + 1}`)"
                                :alt="product.name"
                                class="h-40 w-full object-cover transition duration-300 group-hover:scale-105"
                            />
                        </div>

                        <div class="pt-3">
                            <h3
                                class="min-h-10 text-xs leading-5 font-semibold text-slate-800"
                            >
                                {{ product.name }}
                            </h3>
                            <p class="mt-1 text-[10px] text-slate-500">
                                Clean & Dressed
                            </p>

                            <div class="mt-3 flex flex-wrap items-end gap-2">
                                <span
                                    class="text-xs text-slate-400 line-through"
                                    >{{ product.old }}</span
                                >
                                <span
                                    class="text-base font-black text-[#176536]"
                                    >{{ product.price }}</span
                                >
                                <span class="pb-0.5 text-[10px] text-slate-500"
                                    >/{{ product.unit }}</span
                                >
                            </div>

                            <button
                                class="mt-3 flex w-full items-center justify-center gap-2 rounded-md border border-[#319d57] py-2 text-xs font-bold text-[#176536] transition hover:bg-[#176536] hover:text-white"
                            >
                                Add to Cart
                                <ShoppingCart class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </article>
                </div>

                <div
                    v-if="visibleProducts < products.length"
                    class="mt-6 flex justify-center"
                >
                    <button
                        class="inline-flex items-center gap-2 rounded-md bg-[#176536] px-8 py-3 text-sm font-bold text-white hover:bg-[#15512e]"
                        @click="loadMoreProducts"
                    >
                        Load More Products
                        <ChevronDown class="h-4 w-4" />
                    </button>
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
                                    class="mt-4 inline-block rounded-md bg-[#176536] px-5 py-2.5 text-xs font-bold text-white"
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

        <footer class="mt-2 bg-[#124327] text-white">
            <div
                class="mx-auto grid w-[min(1180px,calc(100%-32px))] gap-9 py-10 sm:grid-cols-2 lg:grid-cols-5"
            >
                <div>
                    <div class="flex items-center gap-2">
                        <div
                            class="grid h-12 w-12 place-items-center rounded-full bg-white text-2xl font-black text-[#15512e]"
                        >
                            F
                        </div>
                        <div class="leading-tight">
                            <div class="text-xl font-black">fresh</div>
                            <div class="-mt-1 text-lg font-bold text-red-400">
                                Today
                            </div>
                        </div>
                    </div>
                    <p class="mt-4 text-xs leading-6 text-[#e0f5e5]">
                        Your trusted online destination for fresh fish, seafood
                        & meat. Quality you can taste, service you can trust.
                    </p>
                    <div class="mt-4 flex gap-3">
                        <a
                            v-for="social in ['f', 'ig', 'yt', 'in']"
                            :key="social"
                            href="#"
                            class="grid h-7 w-7 place-items-center rounded-full border border-white/20 text-[10px] font-black text-[#e0f5e5] uppercase hover:text-white"
                        >
                            {{ social }}
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="mb-4 text-sm font-bold">Quick Links</h4>
                    <ul class="space-y-3 text-xs text-[#e0f5e5]">
                        <li>
                            <a href="#" class="hover:text-white">About Us</a>
                        </li>
                        <li><a href="#" class="hover:text-white">Career</a></li>
                        <li><a href="#" class="hover:text-white">Blog</a></li>
                        <li>
                            <a href="#" class="hover:text-white">Help Center</a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 text-sm font-bold">Customer Service</h4>
                    <ul class="space-y-3 text-xs text-[#e0f5e5]">
                        <li><a href="#" class="hover:text-white">FAQ</a></li>
                        <li>
                            <a href="#" class="hover:text-white"
                                >Return Policy</a
                            >
                        </li>
                        <li>
                            <a href="#" class="hover:text-white"
                                >Shipping Policy</a
                            >
                        </li>
                        <li>
                            <a href="#" class="hover:text-white"
                                >Terms & Conditions</a
                            >
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 text-sm font-bold">Contact Us</h4>
                    <ul class="space-y-3 text-xs leading-5 text-[#e0f5e5]">
                        <li>
                            House: 1/A, Road: 17, South Baridhara<br />R/A,
                            Dhaka - 1212, Bangladesh
                        </li>
                        <li class="flex items-center gap-2">
                            <Phone class="h-3.5 w-3.5" />09617 551122, 01931
                            000700
                        </li>
                        <li class="flex items-center gap-2">
                            <Mail class="h-3.5 w-3.5" />support@freshtodaybd.com
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 text-sm font-bold">We Accept</h4>
                    <div class="grid grid-cols-3 gap-2">
                        <span
                            v-for="method in [
                                'VISA',
                                'MC',
                                'AMEX',
                                'bKash',
                                'Nagad',
                                'Rocket',
                            ]"
                            :key="method"
                            class="rounded bg-white px-2 py-2 text-center text-xs font-black text-[#15512e]"
                        >
                            {{ method }}
                        </span>
                    </div>
                </div>
            </div>

            <div
                class="border-t border-white/10 py-4 text-center text-[11px] text-[#e0f5e5]"
            >
                © 2026 Fresh Today. All Rights Reserved.
            </div>
        </footer>
    </div>
</template>
