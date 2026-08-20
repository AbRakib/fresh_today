<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Check,
    ChevronDown,
    ChevronRight,
    Fish,
    Heart,
    Home,
    Leaf,
    Mail,
    PackageCheck,
    Phone,
    Search,
    ShieldCheck,
    ShoppingCart,
    SlidersHorizontal,
    Truck,
    UserRound,
} from '@lucide/vue';

type Product = {
    name: string;
    weight: string;
    old: string;
    price: string;
    badge?: string;
};

const navItems = [
    'Fresh Fish',
    'Prawn & Shrimp',
    'Hilsha Corner',
    'Crab & Shellfish',
    'Dry Fish',
    'Meat & Poultry',
    'Ready to Cook',
    'Offers',
];

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

const prices = [
    'Under ৳500',
    '৳500 - ৳1,000',
    '৳1,000 - ৳1,500',
    'Above ৳1,500',
];
const weights = [
    'Up to 250g',
    '250g - 500g',
    '500g - 1kg',
    '1kg - 2kg',
    'Above 2kg',
];

const products: Product[] = [
    {
        name: 'Rohu Fish (Rui)',
        weight: '1kg - 1.2kg',
        old: '৳420',
        price: '৳380',
    },
    {
        name: 'Catla Fish (Katal)',
        weight: '1kg - 1.5kg',
        old: '৳460',
        price: '৳420',
    },
    {
        name: 'Hilsha Fish (Ilish)',
        weight: '500g - 700g',
        old: '৳1,800',
        price: '৳1,620',
        badge: '10% OFF',
    },
    { name: 'Pangash Fish', weight: '1kg - 1.2kg', old: '৳320', price: '৳280' },
    { name: 'Tilapia Fish', weight: '500g - 700g', old: '৳260', price: '৳230' },
    {
        name: 'Boal Fish',
        weight: '1kg - 1.5kg',
        old: '৳650',
        price: '৳590',
        badge: '10% OFF',
    },
    { name: 'Rui Fish Steak', weight: '500g', old: '৳340', price: '৳300' },
    {
        name: 'Silver Pomfret',
        weight: '500g - 700g',
        old: '৳580',
        price: '৳520',
        badge: '10% OFF',
    },
    {
        name: 'Prawn (Bagda)',
        weight: '500g',
        old: '৳950',
        price: '৳810',
        badge: '15% OFF',
    },
    { name: 'Deshi Prawn (Golda)', weight: '500g', old: '৳950', price: '৳890' },
    { name: 'Shrimp (Chingri)', weight: '500g', old: '৳620', price: '৳540' },
    { name: 'Crab (Kakra)', weight: '500g - 700g', old: '৳550', price: '৳480' },
    {
        name: 'Sea Bass (Bhetki)',
        weight: '500g - 700g',
        old: '৳780',
        price: '৳690',
        badge: '10% OFF',
    },
    { name: 'Mackerel (Ayre)', weight: '1kg', old: '৳400', price: '৳360' },
    { name: 'Red Snapper', weight: '500g - 700g', old: '৳680', price: '৳600' },
    { name: 'Tuna Fish', weight: '500g - 700g', old: '৳650', price: '৳570' },
    {
        name: 'Shol Fish (Gajar)',
        weight: '500g - 700g',
        old: '৳420',
        price: '৳360',
    },
    {
        name: 'Magur Fish',
        weight: '500g - 700g',
        old: '৳380',
        price: '৳320',
        badge: '15% OFF',
    },
    { name: 'Koi Fish', weight: '500g - 700g', old: '৳360', price: '৳310' },
    { name: 'Sole Fish', weight: '500g - 700g', old: '৳460', price: '৳420' },
    { name: 'Dry Shutki (Lona)', weight: '250g', old: '৳220', price: '৳190' },
    { name: 'Dry Loitta Shutki', weight: '250g', old: '৳240', price: '৳210' },
    {
        name: 'Dry Chingri Shutki',
        weight: '250g',
        old: '৳300',
        price: '৳250',
        badge: '17% OFF',
    },
    { name: 'Dry Kachki Shutki', weight: '250g', old: '৳200', price: '৳170' },
    {
        name: 'Salmon Fillet',
        weight: '500g',
        old: '৳1,250',
        price: '৳1,090',
        badge: '10% OFF',
    },
    { name: 'Fish Finger (Ready)', weight: '250g', old: '৳260', price: '৳220' },
    {
        name: 'Prawn Cutlet (Ready)',
        weight: '250g',
        old: '৳280',
        price: '৳240',
    },
    {
        name: 'Fish Curry Cut (Ready)',
        weight: '500g',
        old: '৳380',
        price: '৳330',
    },
    {
        name: 'Hilsha (Large)',
        weight: '1kg - 1.2kg',
        old: '৳2,200',
        price: '৳1,980',
        badge: '10% OFF',
    },
    { name: 'Rohu (Large)', weight: '1.5kg', old: '৳550', price: '৳490' },
    { name: 'Catla (Large)', weight: '1.5kg', old: '৳600', price: '৳530' },
    {
        name: 'Pangash Fillet',
        weight: '1kg',
        old: '৳520',
        price: '৳450',
        badge: '13% OFF',
    },
];

const imageUrl = (text: string) =>
    `https://placehold.co/420x360/f5f7f4/23833f?text=${encodeURIComponent(text)}`;
</script>

<template>
    <Head title="Fresh Fish & Seafood">
        <link rel="preconnect" href="https://placehold.co" />
    </Head>

    <div class="min-h-screen bg-[#f7faf7] text-[#213228]">
        <div class="bg-[#218a37] text-xs text-white">
            <div
                class="mx-auto flex h-9 w-[min(1180px,calc(100%-32px))] items-center justify-between"
            >
                <div class="flex gap-7">
                    <a href="tel:01712345678" class="flex items-center gap-2">
                        <Phone class="h-3.5 w-3.5" /> 01712 345 678
                    </a>
                    <a
                        href="mailto:hello@freshtoday.com.bd"
                        class="hidden items-center gap-2 sm:flex"
                    >
                        <Mail class="h-3.5 w-3.5" /> hello@freshtoday.com.bd
                    </a>
                </div>
                <div class="flex items-center gap-6">
                    <a href="#">Track Order</a>
                    <a href="#">Help Center</a>
                    <button class="flex items-center gap-1">
                        English <ChevronDown class="h-3 w-3" />
                    </button>
                </div>
            </div>
        </div>

        <header class="bg-white shadow-sm">
            <div
                class="mx-auto flex w-[min(1180px,calc(100%-32px))] items-center gap-8 py-5"
            >
                <Link href="/" class="flex shrink-0 items-center gap-2">
                    <div
                        class="grid h-16 w-16 place-items-center rounded-full bg-[#eef8ee] text-[#218a37]"
                    >
                        <Leaf class="h-8 w-8" />
                    </div>
                    <div class="leading-none">
                        <div class="text-3xl font-black text-[#218a37] italic">
                            Fresh
                        </div>
                        <div
                            class="-mt-1 text-2xl font-black text-red-500 italic"
                        >
                            Today
                        </div>
                    </div>
                </Link>

                <div class="relative flex-1">
                    <input
                        placeholder="Search for fish, seafood and more..."
                        class="h-12 w-full rounded-lg border border-slate-200 bg-white px-5 pr-14 text-sm shadow-sm outline-none focus:border-[#218a37] focus:ring-2 focus:ring-green-100"
                    />
                    <button
                        class="absolute top-0 right-0 grid h-12 w-16 place-items-center rounded-r-lg bg-[#218a37] text-white"
                    >
                        <Search class="h-5 w-5" />
                    </button>
                </div>

                <div class="hidden items-center gap-8 lg:flex">
                    <Link href="/login" class="flex items-center gap-3 text-xs">
                        <UserRound class="h-6 w-6" />
                        <span
                            ><span class="block font-semibold">My Account</span
                            >Sign in / Register</span
                        >
                    </Link>
                    <a
                        href="#"
                        class="relative flex items-center gap-2 text-sm font-semibold"
                    >
                        <ShoppingCart class="h-7 w-7" />
                        <span
                            class="absolute -top-2 left-5 grid h-5 w-5 place-items-center rounded-full bg-[#218a37] text-[10px] text-white"
                            >0</span
                        >
                        Cart
                    </a>
                </div>
            </div>

            <nav class="border-t border-slate-100">
                <div
                    class="mx-auto flex w-[min(1180px,calc(100%-32px))] [scrollbar-width:none] gap-8 overflow-x-auto py-3 text-sm [&::-webkit-scrollbar]:hidden"
                >
                    <a
                        v-for="(item, index) in navItems"
                        :key="item"
                        href="#"
                        class="flex shrink-0 items-center gap-2 pb-2"
                        :class="
                            index === 0
                                ? 'border-b-2 border-[#218a37] font-semibold text-[#218a37]'
                                : 'text-slate-700'
                        "
                    >
                        <Fish class="h-4 w-4" /> {{ item }}
                    </a>
                </div>
            </nav>
        </header>

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
                                                ? 'border-[#218a37] bg-[#218a37]'
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
                        <h2 class="mb-4 text-sm font-bold">Price Range (৳)</h2>
                        <div class="h-1.5 rounded-full bg-[#218a37]"></div>
                        <div
                            class="mt-3 flex justify-between text-xs text-slate-500"
                        >
                            <span>৳ 0</span><span>৳ 2,500</span>
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
                                class="grid h-4 w-4 place-items-center rounded bg-[#218a37]"
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
                            class="mt-4 w-full rounded bg-[#218a37] py-2 text-xs font-bold text-white"
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
                                    class="flex h-6 w-11 items-center rounded-full bg-[#218a37] p-1"
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
                            :key="product.name"
                            class="relative rounded-lg border border-slate-200 bg-white p-2 shadow-sm"
                        >
                            <span
                                v-if="product.badge"
                                class="absolute top-3 left-3 z-10 rounded bg-red-500 px-2 py-1 text-[10px] font-black text-white"
                            >
                                {{ product.badge }}
                            </span>
                            <button
                                class="absolute top-3 right-3 z-10 grid h-7 w-7 place-items-center rounded-full bg-white text-slate-400 shadow"
                            >
                                <Heart class="h-4 w-4" />
                            </button>
                            <img
                                :src="imageUrl(product.name)"
                                :alt="product.name"
                                class="h-44 w-full rounded-md object-cover"
                            />
                            <div class="p-2">
                                <h3 class="text-sm font-bold text-slate-800">
                                    {{ product.name }}
                                </h3>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ product.weight }}
                                </p>
                                <div class="mt-2 flex items-end gap-2">
                                    <span
                                        class="text-xs text-slate-400 line-through"
                                        >{{ product.old }}</span
                                    >
                                    <span
                                        class="text-lg font-black text-[#218a37]"
                                        >{{ product.price }}</span
                                    >
                                </div>
                                <button
                                    class="mt-3 flex w-full items-center justify-center gap-2 rounded border border-[#218a37] py-2 text-xs font-bold text-[#218a37] hover:bg-[#218a37] hover:text-white"
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
                            class="grid h-9 w-9 place-items-center rounded bg-[#218a37] text-sm font-bold text-white"
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

        <footer class="bg-gradient-to-r from-[#06422d] to-[#056d35] text-white">
            <div
                class="mx-auto grid w-[min(1180px,calc(100%-32px))] gap-10 py-10 md:grid-cols-4"
            >
                <div>
                    <div class="flex items-center gap-2">
                        <Leaf class="h-10 w-10" />
                        <div class="text-2xl font-black italic">
                            Fresh<br />Today
                        </div>
                    </div>
                    <p class="mt-4 text-xs leading-6 text-green-50">
                        Your trusted online fish & seafood store in Bangladesh.
                        We deliver freshness to your doorstep.
                    </p>
                    <ul class="mt-4 space-y-2 text-xs text-green-50">
                        <li class="flex items-center gap-2">
                            <Phone class="h-3.5 w-3.5" />01712 345 678
                        </li>
                        <li class="flex items-center gap-2">
                            <Mail class="h-3.5 w-3.5" />hello@freshtoday.com.bd
                        </li>
                        <li class="flex items-center gap-2">
                            <Home class="h-3.5 w-3.5" />House #12, Road #5,
                            Dhaka
                        </li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-5 font-bold">Quick Links</h3>
                    <ul class="space-y-3 text-sm text-green-50">
                        <li>About Us</li>
                        <li>How We Work</li>
                        <li>Delivery Information</li>
                        <li>Returns & Refunds</li>
                        <li>Blog</li>
                        <li>Contact Us</li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-5 font-bold">Categories</h3>
                    <ul class="space-y-3 text-sm text-green-50">
                        <li>Fresh Fish</li>
                        <li>Prawn & Shrimp</li>
                        <li>Hilsha Corner</li>
                        <li>Crab & Shellfish</li>
                        <li>Dry Fish</li>
                        <li>Ready to Cook</li>
                        <li>Meat & Poultry</li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-5 font-bold">Subscribe & Save</h3>
                    <p class="text-sm text-green-50">
                        Get the best offers and updates straight to your inbox.
                    </p>
                    <div class="mt-5 flex overflow-hidden rounded-lg">
                        <input
                            placeholder="Enter your email"
                            class="h-12 flex-1 px-4 text-sm text-slate-900 outline-none"
                        />
                        <button class="bg-[#31a846] px-5 text-sm font-bold">
                            Subscribe
                        </button>
                    </div>
                    <h4 class="mt-8 mb-3 text-sm font-bold">We Accept</h4>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="method in [
                                'VISA',
                                'MC',
                                'bKash',
                                'Nagad',
                                'AMEX',
                            ]"
                            :key="method"
                            class="rounded bg-white px-3 py-2 text-xs font-black text-[#056d35]"
                            >{{ method }}</span
                        >
                    </div>
                </div>
            </div>
            <div
                class="mx-auto flex w-[min(1180px,calc(100%-32px))] justify-between border-t border-white/10 py-5 text-xs text-green-50"
            >
                <span>© 2024 Fresh Today. All rights reserved.</span>
                <span>Terms & Conditions &nbsp; | &nbsp; Privacy Policy</span>
            </div>
        </footer>
    </div>
</template>
