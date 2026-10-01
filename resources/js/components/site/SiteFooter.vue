<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Mail, Phone } from '@lucide/vue';
import { computed } from 'vue';

type SharedSettings = {
    address: string | null;
    email: string | null;
    phone: string | null;
};

const page = usePage<{ settings?: SharedSettings }>();

const settingsAddress = computed(
    () => page.props.settings?.address?.trim() || '',
);
const settingsPhone = computed(() => page.props.settings?.phone?.trim() || '');
const settingsEmail = computed(() => page.props.settings?.email?.trim() || '');

const socialLinks = [
    {
        name: 'Facebook',
        href: '#',
        viewBox: '0 0 24 24',
        path: 'M14 8.5h2V5.2c-.3 0-1.4-.1-2.7-.1-2.7 0-4.5 1.6-4.5 4.6v2.8H6v3.7h2.8V24h3.7v-7.8h3.1l.5-3.7h-3.6V10c0-1 .3-1.5 1.5-1.5Z',
    },
    {
        name: 'Twitter',
        href: '#',
        viewBox: '0 0 24 24',
        path: 'M21.5 6.1c-.7.3-1.5.5-2.3.6.8-.5 1.5-1.3 1.8-2.2-.8.5-1.7.8-2.6 1A4.05 4.05 0 0 0 11.3 8c0 .3 0 .6.1.9-3.4-.2-6.4-1.8-8.4-4.3-.4.6-.5 1.3-.5 2 0 1.4.7 2.6 1.8 3.3-.7 0-1.3-.2-1.8-.5v.1c0 2 1.4 3.6 3.2 4-.3.1-.7.1-1.1.1-.3 0-.5 0-.8-.1.5 1.6 2 2.8 3.8 2.8A8.1 8.1 0 0 1 2.5 18c-.3 0-.7 0-1-.1A11.4 11.4 0 0 0 7.8 20c7.5 0 11.7-6.2 11.7-11.7v-.5c.8-.5 1.5-1.1 2-1.7Z',
    },
    {
        name: 'YouTube',
        href: '#',
        viewBox: '0 0 24 24',
        path: 'M23.5 7.2s-.2-1.6-.9-2.3c-.9-.9-1.8-.9-2.3-1C17.1 3.7 12 3.7 12 3.7s-5.1 0-8.3.2c-.5.1-1.4.1-2.3 1C.7 5.6.5 7.2.5 7.2S.3 9.1.3 11v1.8c0 1.9.2 3.8.2 3.8s.2 1.6.9 2.3c.9.9 2 .9 2.5 1 1.8.2 8.1.2 8.1.2s5.1 0 8.3-.3c.5 0 1.4-.1 2.3-1 .7-.7.9-2.3.9-2.3s.2-1.9.2-3.8V11c0-1.9-.2-3.8-.2-3.8ZM9.5 15.2V8.6l6.5 3.3-6.5 3.3Z',
    },
    {
        name: 'LinkedIn',
        href: '#',
        viewBox: '0 0 24 24',
        path: 'M5.3 7.9H1.5V24h3.8V7.9ZM3.4 0C2.1 0 1.2.9 1.2 2.1s.9 2.1 2.2 2.1 2.2-.9 2.2-2.1S4.7 0 3.4 0Zm19.4 14.8c0-4.3-2.3-6.3-5.3-6.3-2.4 0-3.5 1.3-4.1 2.3V7.9H9.6V24h3.8v-8c0-.4 0-.9.2-1.2.4-.9 1.2-1.7 2.7-1.7 1.9 0 2.7 1.4 2.7 3.6V24h3.8v-9.2Z',
    },
];
</script>

<template>
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
                    Your trusted online destination for fresh fish, seafood &
                    meat. Quality you can taste, service you can trust.
                </p>
                <div class="mt-4 flex gap-3">
                    <a
                        v-for="social in socialLinks"
                        :key="social.name"
                        :href="social.href"
                        :aria-label="social.name"
                        class="grid h-7 w-7 place-items-center rounded-full border border-white/20 text-[#e0f5e5] transition hover:border-white/40 hover:text-white"
                    >
                        <svg
                            class="h-3.5 w-3.5 fill-current"
                            aria-hidden="true"
                            :viewBox="social.viewBox"
                        >
                            <path :d="social.path" />
                        </svg>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="mb-4 text-sm font-bold">Quick Links</h4>
                <ul class="space-y-3 text-xs text-[#e0f5e5]">
                    <li>
                        <Link href="/about" class="hover:text-white">
                            About Us
                        </Link>
                    </li>
                    <li><a href="#" class="hover:text-white">Career</a></li>
                    <li><a href="#" class="hover:text-white">Blog</a></li>
                    <li>
                        <Link href="/help-center" class="hover:text-white">
                            Help Center
                        </Link>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="mb-4 text-sm font-bold">Customer Service</h4>
                <ul class="space-y-3 text-xs text-[#e0f5e5]">
                    <li>
                        <Link href="/faq" class="hover:text-white">FAQ</Link>
                    </li>
                    <li>
                        <Link href="/return-policy" class="hover:text-white">
                            Return Policy
                        </Link>
                    </li>
                    <li>
                        <Link href="/shipping-policy" class="hover:text-white">
                            Shipping Policy
                        </Link>
                    </li>
                    <li>
                        <Link
                            href="/terms-and-conditions"
                            class="hover:text-white"
                        >
                            Terms & Conditions
                        </Link>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="mb-4 text-sm font-bold">Contact Us</h4>
                <ul class="space-y-3 text-xs leading-5 text-[#e0f5e5]">
                    <li v-if="settingsAddress" class="whitespace-pre-line">
                        {{ settingsAddress }}
                    </li>
                    <li v-if="settingsPhone" class="flex items-center gap-2">
                        <Phone class="h-3.5 w-3.5" />{{ settingsPhone }}
                    </li>
                    <li v-if="settingsEmail" class="flex items-center gap-2">
                        <Mail class="h-3.5 w-3.5" />{{ settingsEmail }}
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
</template>
