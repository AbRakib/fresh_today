<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Clock3,
    Fish,
    HeartHandshake,
    Leaf,
    MapPin,
    PackageCheck,
    ShieldCheck,
    Truck,
} from '@lucide/vue';
import { computed } from 'vue';
import SiteFooter from '@/components/site/SiteFooter.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';

type SharedSettings = {
    address: string | null;
    email: string | null;
    phone: string | null;
};

const page = usePage<{ settings?: SharedSettings }>();

const settingsAddress = computed(
    () => page.props.settings?.address?.trim() || 'Dhaka, Bangladesh',
);

const promises = [
    {
        icon: Fish,
        title: 'Fresh Selection',
        text: 'Fish, seafood, and meat are chosen with care for everyday family meals.',
    },
    {
        icon: ShieldCheck,
        title: 'Clean Handling',
        text: 'Every order is cleaned, packed, and prepared with hygiene in mind.',
    },
    {
        icon: Truck,
        title: 'Reliable Delivery',
        text: 'We focus on timely delivery so fresh food reaches your kitchen quickly.',
    },
];

const values = [
    {
        icon: Leaf,
        title: 'Source Responsibly',
        text: 'We work to keep quality consistent from market selection to doorstep delivery.',
    },
    {
        icon: PackageCheck,
        title: 'Pack Carefully',
        text: 'Orders are portioned and packed so families can store, cook, and serve with ease.',
    },
    {
        icon: HeartHandshake,
        title: 'Serve Honestly',
        text: 'Clear product details, fair prices, and helpful support guide every order.',
    },
];
</script>

<template>
    <Head title="About Us - Fresh Today" />

    <div class="min-h-screen bg-white font-sans text-slate-800">
        <SiteHeader />

        <main>
            <section class="bg-[#f7fbf4]">
                <div
                    class="mx-auto grid w-[min(1180px,calc(100%-32px))] gap-10 py-14 lg:grid-cols-[1fr_0.85fr] lg:items-center"
                >
                    <div>
                        <span
                            class="text-sm font-bold tracking-wide text-[#218a37] uppercase"
                            >About Fresh Today</span
                        >
                        <h1
                            class="mt-3 max-w-3xl text-4xl leading-tight font-black text-[#15512e] sm:text-5xl"
                        >
                            Fresh food, carefully selected for your family.
                        </h1>
                        <p
                            class="mt-5 max-w-2xl text-base leading-8 text-slate-600"
                        >
                            Fresh Today is an online destination for quality
                            fish, seafood, and meat. We help busy households
                            shop confidently by bringing fresh products, careful
                            packing, and dependable service into one simple
                            experience.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <Link
                                href="/shop"
                                class="inline-flex items-center rounded-md bg-lime-500 px-6 py-3 text-sm font-bold text-white shadow hover:bg-lime-600"
                            >
                                Shop Fresh Products
                            </Link>
                            <Link
                                href="/"
                                class="inline-flex items-center rounded-md border border-[#218a37] px-6 py-3 text-sm font-bold text-[#15512e] hover:bg-white"
                            >
                                Back to Home
                            </Link>
                        </div>
                    </div>

                    <div
                        class="grid gap-4 rounded-2xl bg-white p-5 shadow-[0_8px_30px_rgba(0,0,0,.07)]"
                    >
                        <div
                            class="grid min-h-48 place-items-center rounded-xl bg-[#15512e] px-6 py-8 text-center text-white"
                        >
                            <Fish class="h-16 w-16 text-lime-300" />
                            <div>
                                <div class="mt-4 text-3xl font-black">
                                    Fresh Today
                                </div>
                                <p class="mt-2 text-sm text-[#e0f5e5]">
                                    Quality seafood and meat, delivered with
                                    care.
                                </p>
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-lg bg-[#f7fbf4] p-4">
                                <Clock3 class="h-6 w-6 text-[#218a37]" />
                                <div
                                    class="mt-3 text-2xl font-black text-[#15512e]"
                                >
                                    Daily
                                </div>
                                <p class="text-xs leading-5 text-slate-600">
                                    Fresh sourcing and order preparation.
                                </p>
                            </div>
                            <div class="rounded-lg bg-[#f7fbf4] p-4">
                                <MapPin class="h-6 w-6 text-[#218a37]" />
                                <div
                                    class="mt-3 text-2xl font-black text-[#15512e]"
                                >
                                    Local
                                </div>
                                <p class="text-xs leading-5 text-slate-600">
                                    Serving customers from
                                    {{ settingsAddress }}.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section
                class="mx-auto grid w-[min(1180px,calc(100%-32px))] gap-5 py-12 md:grid-cols-3"
            >
                <article
                    v-for="promise in promises"
                    :key="promise.title"
                    class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <component
                        :is="promise.icon"
                        class="h-8 w-8 text-[#218a37]"
                    />
                    <h2 class="mt-5 text-xl font-black text-[#15512e]">
                        {{ promise.title }}
                    </h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        {{ promise.text }}
                    </p>
                </article>
            </section>

            <section class="bg-slate-50">
                <div
                    class="mx-auto grid w-[min(1180px,calc(100%-32px))] gap-10 py-12 lg:grid-cols-[0.85fr_1fr] lg:items-start"
                >
                    <div>
                        <span
                            class="text-sm font-bold tracking-wide text-[#218a37] uppercase"
                            >Our Story</span
                        >
                        <h2
                            class="mt-3 text-3xl leading-tight font-black text-[#15512e]"
                        >
                            Built for easier, more trustworthy grocery days.
                        </h2>
                        <p class="mt-5 text-sm leading-7 text-slate-600">
                            We know fresh food shopping takes trust. Fresh Today
                            was created to make that process more convenient,
                            transparent, and dependable for homes that want
                            quality without spending the whole day at the
                            market.
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            v-for="value in values"
                            :key="value.title"
                            class="rounded-lg bg-white p-5 shadow-sm"
                        >
                            <component
                                :is="value.icon"
                                class="h-7 w-7 text-[#218a37]"
                            />
                            <h3 class="mt-4 font-black text-[#15512e]">
                                {{ value.title }}
                            </h3>
                            <p class="mt-2 text-xs leading-6 text-slate-600">
                                {{ value.text }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto w-[min(1180px,calc(100%-32px))] py-12">
                <div
                    class="flex flex-col gap-5 rounded-2xl bg-[#15512e] px-7 py-8 text-white sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <div class="flex items-center gap-2 text-lime-300">
                            <BadgeCheck class="h-5 w-5" />
                            <span class="text-sm font-bold uppercase"
                                >Ready to order?</span
                            >
                        </div>
                        <h2 class="mt-3 text-2xl font-black">
                            Bring Fresh Today to your table.
                        </h2>
                    </div>
                    <Link
                        href="/shop"
                        class="inline-flex w-fit items-center rounded-md bg-lime-500 px-6 py-3 text-sm font-bold text-white shadow hover:bg-lime-600"
                    >
                        Browse Shop
                    </Link>
                </div>
            </section>
        </main>

        <SiteFooter />
    </div>
</template>
