<script setup lang="ts">
import { Mail, Phone } from '@lucide/vue';
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
                    <li><a href="#" class="hover:text-white">About Us</a></li>
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
                        <a href="#" class="hover:text-white">Return Policy</a>
                    </li>
                    <li>
                        <a href="#" class="hover:text-white">Shipping Policy</a>
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
                        House: 1/A, Road: 17, South Baridhara<br />R/A, Dhaka -
                        1212, Bangladesh
                    </li>
                    <li class="flex items-center gap-2">
                        <Phone class="h-3.5 w-3.5" />09617 551122, 01931 000700
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
</template>
