<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ChevronRight,
    Clock3,
    CreditCard,
    MapPin,
    PackageCheck,
    ShoppingBag,
    UserRound,
} from '@lucide/vue';
import { computed } from 'vue';
import SiteFooter from '@/components/site/SiteFooter.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';
import { useCurrency } from '@/composables/useCurrency';

type OrderDetail = {
    id: number;
    product_name: string;
    product_sku: string | null;
    thumbnail_url: string | null;
    order_qty: number;
    sale_price: string;
    total_amount: string;
};

type CustomerOrder = {
    id: number;
    order_number: string;
    total_product: number;
    subtotal: string;
    delivery_charge: string;
    total_amount: string;
    paid_amount: string;
    due_amount: string;
    payment_status: number;
    order_status: number;
    order_date: string | null;
    delivery_date: string | null;
    delivery_address: string | null;
    note: string | null;
    details: OrderDetail[];
};

type FrontendCustomer = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    address?: string | null;
    avatar?: string | null;
};

const props = defineProps<{
    orders: CustomerOrder[];
}>();

const page = usePage<{
    auth: {
        customer: FrontendCustomer | null;
    };
}>();
const { money } = useCurrency();
const customer = computed(() => page.props.auth.customer);
const latestOrder = computed(() => props.orders[0] ?? null);
const totalSpent = computed(() =>
    props.orders.reduce((total, order) => total + Number(order.paid_amount), 0),
);
const totalDue = computed(() =>
    props.orders.reduce((total, order) => total + Number(order.due_amount), 0),
);
const pendingOrders = computed(
    () => props.orders.filter((order) => order.order_status < 2).length,
);
const formatDate = (value: string | null) =>
    value
        ? new Intl.DateTimeFormat(undefined, {
              year: 'numeric',
              month: 'short',
              day: 'numeric',
          }).format(new Date(`${value}T00:00:00`))
        : 'Not set';
const statusLabel = (status: number) =>
    ['Pending', 'Processing', 'Delivered', 'Cancelled'][status] ?? 'Unknown';
const paymentLabel = (status: number) =>
    ['Unpaid', 'Paid', 'Partial'][status] ?? 'Unknown';
const imageUrl = (text: string) =>
    `https://placehold.co/96x96/f5f7f4/23833f?text=${encodeURIComponent(text)}`;
</script>

<template>
    <Head title="Customer Dashboard">
        <link rel="preconnect" href="https://placehold.co" />
    </Head>

    <div class="min-h-screen bg-[#f7faf7] text-[#213228]">
        <SiteHeader />

        <main class="mx-auto w-[min(1180px,calc(100%-32px))] py-8">
            <div class="mb-6 flex items-center gap-2 text-xs text-slate-500">
                <Link href="/" class="hover:text-[#218a37]">Home</Link>
                <ChevronRight class="h-3 w-3" />
                <span class="text-slate-700">Customer Dashboard</span>
            </div>

            <section
                class="mb-6 grid gap-4 rounded-lg border border-slate-200 bg-white p-5 shadow-sm lg:grid-cols-[1fr_auto]"
            >
                <div class="flex gap-4">
                    <div
                        class="grid h-14 w-14 shrink-0 place-items-center rounded-full bg-[#e7f7de] text-[#176536]"
                    >
                        <UserRound class="h-7 w-7" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[#218a37]">
                            Customer account
                        </p>
                        <h1 class="mt-1 text-2xl font-black text-[#1b2f24]">
                            {{ customer?.name ?? 'My Dashboard' }}
                        </h1>
                        <p class="mt-2 max-w-2xl text-sm text-slate-600">
                            {{ customer?.email }}
                            <span v-if="customer?.phone"> · {{ customer.phone }}</span>
                        </p>
                    </div>
                </div>
                <Link
                    href="/shop"
                    class="self-start rounded bg-lime-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-lime-600"
                >
                    Continue Shopping
                </Link>
            </section>

            <section class="mb-6 grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <ShoppingBag class="mb-3 h-5 w-5 text-[#218a37]" />
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Orders
                    </p>
                    <p class="mt-1 text-2xl font-black">{{ orders.length }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <Clock3 class="mb-3 h-5 w-5 text-[#218a37]" />
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Active
                    </p>
                    <p class="mt-1 text-2xl font-black">{{ pendingOrders }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <CreditCard class="mb-3 h-5 w-5 text-[#218a37]" />
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Paid
                    </p>
                    <p class="mt-1 text-2xl font-black">
                        {{ money(totalSpent) }}
                    </p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <PackageCheck class="mb-3 h-5 w-5 text-[#218a37]" />
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Due
                    </p>
                    <p class="mt-1 text-2xl font-black">{{ money(totalDue) }}</p>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[320px_1fr]">
                <aside class="space-y-4">
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-black text-[#1b2f24]">
                            Profile Details
                        </h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-500">
                                    Name
                                </dt>
                                <dd class="mt-1 font-semibold">
                                    {{ customer?.name ?? 'Not provided' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-500">
                                    Email
                                </dt>
                                <dd class="mt-1 break-all font-semibold">
                                    {{ customer?.email ?? 'Not provided' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-500">
                                    Phone
                                </dt>
                                <dd class="mt-1 font-semibold">
                                    {{ customer?.phone ?? 'Not provided' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-500">
                                    Address
                                </dt>
                                <dd class="mt-1 font-semibold">
                                    {{ customer?.address ?? 'Not provided' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div
                        v-if="latestOrder"
                        class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <h2 class="text-lg font-black text-[#1b2f24]">
                            Latest Order
                        </h2>
                        <p class="mt-2 text-sm font-bold text-[#218a37]">
                            {{ latestOrder.order_number }}
                        </p>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ formatDate(latestOrder.order_date) }}
                        </p>
                        <div class="mt-4 flex items-start gap-2 text-sm text-slate-600">
                            <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-[#218a37]" />
                            <span>{{ latestOrder.delivery_address ?? 'No delivery address' }}</span>
                        </div>
                    </div>
                </aside>

                <section class="space-y-4">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-xl font-black text-[#1b2f24]">
                            Order Profile
                        </h2>
                        <span class="text-sm text-slate-500">
                            {{ orders.length }} total
                        </span>
                    </div>

                    <article
                        v-for="order in orders"
                        :key="order.id"
                        class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="font-black text-[#1b2f24]">
                                    {{ order.order_number }}
                                </h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    Ordered {{ formatDate(order.order_date) }}
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    class="rounded-full bg-[#e7f7de] px-3 py-1 text-xs font-bold text-[#176536]"
                                >
                                    {{ statusLabel(order.order_status) }}
                                </span>
                                <span
                                    class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700"
                                >
                                    {{ paymentLabel(order.payment_status) }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 divide-y divide-slate-100">
                            <div
                                v-for="detail in order.details"
                                :key="detail.id"
                                class="flex items-center gap-3 py-3"
                            >
                                <img
                                    :src="detail.thumbnail_url ?? imageUrl(detail.product_name)"
                                    :alt="detail.product_name"
                                    class="h-14 w-14 rounded-md object-cover"
                                />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-slate-800">
                                        {{ detail.product_name }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        Qty {{ detail.order_qty }} · {{ money(detail.sale_price) }}
                                    </p>
                                </div>
                                <p class="text-sm font-black text-[#1b2f24]">
                                    {{ money(detail.total_amount) }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-4 grid gap-3 border-t border-slate-100 pt-4 text-sm sm:grid-cols-4"
                        >
                            <div>
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Subtotal
                                </p>
                                <p class="mt-1 font-bold">{{ money(order.subtotal) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Delivery
                                </p>
                                <p class="mt-1 font-bold">
                                    {{ money(order.delivery_charge) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Paid
                                </p>
                                <p class="mt-1 font-bold">{{ money(order.paid_amount) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Total
                                </p>
                                <p class="mt-1 font-black">{{ money(order.total_amount) }}</p>
                            </div>
                        </div>
                    </article>

                    <div
                        v-if="!orders.length"
                        class="rounded-lg border border-dashed border-slate-300 bg-white p-10 text-center"
                    >
                        <ShoppingBag class="mx-auto h-12 w-12 text-slate-300" />
                        <h2 class="mt-4 text-xl font-black text-[#1b2f24]">
                            No orders yet
                        </h2>
                        <p class="mt-2 text-sm text-slate-600">
                            Your placed orders will appear here.
                        </p>
                        <Link
                            href="/shop"
                            class="mt-5 inline-flex rounded bg-lime-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-lime-600"
                        >
                            Start Shopping
                        </Link>
                    </div>
                </section>
            </section>
        </main>

        <SiteFooter />
    </div>
</template>
