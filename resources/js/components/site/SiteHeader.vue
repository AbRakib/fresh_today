<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ChevronDown,
    Fish,
    Mail,
    Phone,
    Search,
    ShoppingCart,
    UserRound,
} from '@lucide/vue';
import { dashboard, login, register } from '@/routes';

const categoryLinks = [
    'Fresh Fish',
    'Prawn & Shrimp',
    'Hilsha Corner',
    'Crab & Shellfish',
    'Dry Fish',
    'Meat & Poultry',
    'Ready to Cook',
    'Offers',
];
</script>

<template>
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

            <nav class="hidden items-center gap-8 lg:flex">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="flex items-center gap-3 text-xs leading-tight text-black hover:text-[#176536]"
                >
                    <UserRound class="h-6 w-6 shrink-0" />
                    <span>
                        <span class="block font-bold">My Account</span>
                        <span class="block">Dashboard</span>
                    </span>
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="flex items-center gap-3 text-xs leading-tight text-black hover:text-[#176536]"
                    >
                        <UserRound class="h-6 w-6 shrink-0" />
                        <span>
                            <span class="block font-bold">My Account</span>
                            <span class="block">Sign in / Register</span>
                        </span>
                    </Link>
                    <Link :href="register()" class="sr-only">Register</Link>
                </template>
                <a
                    href="#"
                    class="relative flex items-center gap-2 text-sm font-bold text-black hover:text-[#176536]"
                >
                    <span class="relative">
                        <ShoppingCart class="h-7 w-7" />
                        <span
                            class="absolute -top-3 left-5 grid h-5 w-5 place-items-center rounded-full bg-[#218a37] text-[10px] font-bold text-white"
                            >0</span
                        >
                    </span>
                    <span class="text-base leading-none sm:text-sm xl:text-base"
                        >Cart</span
                    >
                </a>
            </nav>
        </div>

        <div class="border-t border-slate-100">
            <div
                class="mx-auto flex w-[min(1180px,calc(100%-32px))] [scrollbar-width:none] gap-8 overflow-x-auto py-3 text-sm [&::-webkit-scrollbar]:hidden"
            >
                <Link
                    v-for="(link, index) in categoryLinks"
                    :key="link"
                    href="/fresh-fish"
                    class="flex shrink-0 items-center gap-2 pb-2"
                    :class="
                        index === 0
                            ? 'border-b-2 border-[#218a37] font-semibold text-[#218a37]'
                            : 'text-slate-700 hover:text-[#218a37]'
                    "
                >
                    <Fish class="h-4 w-4" /> {{ link }}
                </Link>
            </div>
        </div>
    </header>
</template>
