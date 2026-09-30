<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CircleHelp,
    ClipboardCheck,
    FileText,
    LifeBuoy,
    RotateCcw,
    ShieldCheck,
    Truck,
} from '@lucide/vue';
import { computed } from 'vue';
import SiteFooter from '@/components/site/SiteFooter.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';

type StaticPageSection = {
    title: string;
    items: string[];
};

type StaticPage = {
    title: string;
    eyebrow: string;
    intro: string;
    sections: StaticPageSection[];
};

const props = defineProps<{
    page: StaticPage;
}>();

const icon = computed(() => {
    const icons = {
        'Help Center': LifeBuoy,
        FAQ: CircleHelp,
        'Return Policy': RotateCcw,
        'Shipping Policy': Truck,
        'Terms & Conditions': FileText,
    };

    return icons[props.page.title as keyof typeof icons] ?? ShieldCheck;
});
</script>

<template>
    <Head :title="`${page.title} - Fresh Today`" />

    <div class="min-h-screen bg-white font-sans text-slate-800">
        <SiteHeader />

        <main>
            <section class="bg-[#f7fbf4]">
                <div class="mx-auto w-[min(1180px,calc(100%-32px))] py-12">
                    <Link
                        href="/about"
                        class="inline-flex items-center gap-2 text-sm font-bold text-[#176536] hover:text-[#15512e]"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        About Fresh Today
                    </Link>

                    <div
                        class="mt-8 grid gap-8 lg:grid-cols-[0.8fr_1fr] lg:items-center"
                    >
                        <div>
                            <span
                                class="text-sm font-bold tracking-wide text-[#218a37] uppercase"
                                >{{ page.eyebrow }}</span
                            >
                            <h1
                                class="mt-3 text-4xl leading-tight font-black text-[#15512e] sm:text-5xl"
                            >
                                {{ page.title }}
                            </h1>
                            <p
                                class="mt-5 max-w-2xl text-base leading-8 text-slate-600"
                            >
                                {{ page.intro }}
                            </p>
                        </div>

                        <div
                            class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm"
                        >
                            <component
                                :is="icon"
                                class="h-12 w-12 text-[#218a37]"
                            />
                            <h2 class="mt-5 text-2xl font-black text-[#15512e]">
                                Fresh Today Support
                            </h2>
                            <p class="mt-3 text-sm leading-7 text-slate-600">
                                We keep our policies clear so you can shop
                                confidently and know what to expect before,
                                during, and after delivery.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section
                class="mx-auto grid w-[min(1180px,calc(100%-32px))] gap-5 py-12 md:grid-cols-2"
            >
                <article
                    v-for="section in page.sections"
                    :key="section.title"
                    class="rounded-lg border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <ClipboardCheck class="h-6 w-6 text-[#218a37]" />
                        <h2 class="text-xl font-black text-[#15512e]">
                            {{ section.title }}
                        </h2>
                    </div>
                    <ul class="mt-5 space-y-4 text-sm leading-7 text-slate-600">
                        <li
                            v-for="item in section.items"
                            :key="item"
                            class="flex gap-3"
                        >
                            <ShieldCheck
                                class="mt-1 h-4 w-4 shrink-0 text-lime-500"
                            />
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                </article>
            </section>

            <section class="mx-auto w-[min(1180px,calc(100%-32px))] pb-12">
                <div
                    class="flex flex-col gap-5 rounded-lg bg-[#15512e] px-7 py-8 text-white sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <div class="text-sm font-bold text-lime-300 uppercase">
                            Need more help?
                        </div>
                        <h2 class="mt-2 text-2xl font-black">
                            Our team is ready to support your fresh order.
                        </h2>
                    </div>
                    <Link
                        href="/shop"
                        class="inline-flex w-fit items-center rounded-md bg-lime-500 px-6 py-3 text-sm font-bold text-white shadow hover:bg-lime-600"
                    >
                        Continue Shopping
                    </Link>
                </div>
            </section>
        </main>

        <SiteFooter />
    </div>
</template>
