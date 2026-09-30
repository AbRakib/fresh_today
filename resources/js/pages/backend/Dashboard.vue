<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowDownLeft,
    ArrowUpRight,
    Banknote,
    Boxes,
    CalendarDays,
    CreditCard,
    PackagePlus,
    ReceiptText,
    ShoppingBag,
    TrendingUp,
    Truck,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import { dashboard } from '@/routes';
import { useCurrency } from '@/composables/useCurrency';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

type Summary = {
    today_sales: number;
    today_orders: number;
    month_sales: number;
    month_purchases: number;
    order_due: number;
    purchase_due: number;
    products: number;
    active_products: number;
    customers: number;
    suppliers: number;
    stock_value: number;
    cash_balance: number;
};
type TrendPoint = {
    label: string;
    date: string;
    sales: number;
    orders: number;
};
type OrderStatus = {
    label: string;
    value: number;
    class: string;
    count: number;
};
type RecentOrder = {
    id: number;
    order_number: string;
    customer_name: string;
    customer_phone: string | null;
    total_amount: number;
    due_amount: number;
    payment_status: number;
    order_status: number;
    order_date: string | null;
};
type LowStockProduct = {
    id: number;
    name: string;
    sku: string | null;
    stock_quantity: number;
    minimum_order_quantity: number;
    unit: string | null;
};
type TopCategory = {
    id: number;
    name: string;
    products_count: number;
};
type RecentTransaction = {
    id: number;
    transaction_no: string;
    date: string | null;
    account_name: string | null;
    description: string | null;
    transaction_type: number;
    total_amount: number;
};

const props = defineProps<{
    summary: Summary;
    salesTrend: TrendPoint[];
    orderStatuses: OrderStatus[];
    recentOrders: RecentOrder[];
    lowStockProducts: LowStockProduct[];
    topCategories: TopCategory[];
    recentTransactions: RecentTransaction[];
}>();

const { money } = useCurrency();
const maxSales = computed(() =>
    Math.max(...props.salesTrend.map((day) => day.sales), 1),
);
const maxCategoryProducts = computed(() =>
    Math.max(
        ...props.topCategories.map((category) => category.products_count),
        1,
    ),
);
const totalOrderStatuses = computed(() =>
    props.orderStatuses.reduce((total, status) => total + status.count, 0),
);
const activeProductRate = computed(() =>
    props.summary.products > 0
        ? Math.round(
              (props.summary.active_products / props.summary.products) * 100,
          )
        : 0,
);
const netMonth = computed(
    () => props.summary.month_sales - props.summary.month_purchases,
);

const formatNumber = (value: number) =>
    new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 }).format(
        value,
    );
const formatDate = (value: string | null) =>
    value
        ? new Intl.DateTimeFormat(undefined, {
              month: 'short',
              day: 'numeric',
              year: 'numeric',
          }).format(new Date(`${value}T00:00:00`))
        : 'Not set';
const orderStatusLabel = (status: number) =>
    ['Pending', 'Processing', 'Delivered', 'Cancelled'][status] ?? 'Unknown';
const paymentStatusLabel = (status: number) =>
    ['Unpaid', 'Paid', 'Partial'][status] ?? 'Unknown';
const paymentStatusClass = (status: number) =>
    status === 1
        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20'
        : status === 2
          ? 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20'
          : 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/20';
const orderStatusClass = (status: number) =>
    status === 2
        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20'
        : status === 1
          ? 'bg-sky-50 text-sky-700 ring-sky-200 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/20'
          : status === 3
            ? 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/20'
            : 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20';

const kpis = computed(() => [
    {
        label: 'Today Sales',
        value: money(props.summary.today_sales),
        meta: `${formatNumber(props.summary.today_orders)} orders today`,
        icon: ShoppingBag,
        tone: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10 dark:text-emerald-300',
    },
    {
        label: 'Month Sales',
        value: money(props.summary.month_sales),
        meta: `${netMonth.value >= 0 ? '+' : ''}${money(netMonth.value)} after purchases`,
        icon: TrendingUp,
        tone: 'text-sky-600 bg-sky-50 dark:bg-sky-500/10 dark:text-sky-300',
    },
    {
        label: 'Receivable Due',
        value: money(props.summary.order_due),
        meta: 'Pending customer payments',
        icon: CreditCard,
        tone: 'text-amber-600 bg-amber-50 dark:bg-amber-500/10 dark:text-amber-300',
    },
    {
        label: 'Cash Balance',
        value: money(props.summary.cash_balance),
        meta: `${money(props.summary.purchase_due)} supplier due`,
        icon: Banknote,
        tone: 'text-violet-600 bg-violet-50 dark:bg-violet-500/10 dark:text-violet-300',
    },
]);

const quickLinks = [
    { label: 'New Order', href: '/orders/create', icon: ReceiptText },
    { label: 'Add Product', href: '/products/create', icon: PackagePlus },
    { label: 'Purchases', href: '/purchases', icon: Truck },
    { label: 'Stock Report', href: '/reports/stock', icon: Boxes },
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4 sm:p-6">
        <section
            class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
        >
            <div>
                <h1
                    class="text-2xl font-semibold tracking-normal text-foreground sm:text-3xl"
                >
                    Fresh Today Dashboard
                </h1>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:flex">
                <Link
                    v-for="link in quickLinks"
                    :key="link.href"
                    :href="link.href"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-border bg-background px-3 text-sm font-medium text-foreground shadow-sm transition hover:bg-muted"
                >
                    <component :is="link.icon" class="size-4" />
                    <span>{{ link.label }}</span>
                </Link>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article
                v-for="kpi in kpis"
                :key="kpi.label"
                class="rounded-lg border border-border bg-card p-5 shadow-sm"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">
                            {{ kpi.label }}
                        </p>
                        <p
                            class="mt-2 text-2xl font-semibold text-card-foreground"
                        >
                            {{ kpi.value }}
                        </p>
                    </div>
                    <div
                        :class="[
                            'grid size-11 place-items-center rounded-md',
                            kpi.tone,
                        ]"
                    >
                        <component :is="kpi.icon" class="size-5" />
                    </div>
                </div>
                <p class="mt-4 text-sm text-muted-foreground">{{ kpi.meta }}</p>
            </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.45fr_0.95fr]">
            <article
                class="rounded-lg border border-border bg-card p-5 shadow-sm"
            >
                <div
                    class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-base font-semibold text-card-foreground"
                        >
                            7 Day Sales Trend
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Daily order value with order count.
                        </p>
                    </div>
                    <div
                        class="mt-2 inline-flex items-center gap-2 text-sm font-medium text-emerald-600 sm:mt-0 dark:text-emerald-300"
                    >
                        <CalendarDays class="size-4" />
                        Last 7 days
                    </div>
                </div>
                <div
                    class="mt-6 flex h-72 items-end gap-3 border-b border-l border-border px-2 pb-3 sm:gap-4"
                >
                    <div
                        v-for="day in salesTrend"
                        :key="day.date"
                        class="flex min-w-0 flex-1 flex-col items-center gap-3"
                    >
                        <div class="flex h-52 w-full items-end justify-center">
                            <div
                                class="group relative w-full max-w-12 rounded-t-md bg-emerald-500 transition hover:bg-emerald-600"
                                :style="{
                                    height: `${Math.max(8, (day.sales / maxSales) * 100)}%`,
                                }"
                            >
                                <div
                                    class="pointer-events-none absolute bottom-full left-1/2 mb-2 hidden -translate-x-1/2 rounded-md bg-foreground px-2 py-1 text-xs whitespace-nowrap text-background shadow-lg group-hover:block"
                                >
                                    {{ money(day.sales) }} /
                                    {{ day.orders }} orders
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-medium text-foreground">
                                {{ day.label }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ day.orders }}
                            </p>
                        </div>
                    </div>
                </div>
            </article>

            <article
                class="rounded-lg border border-border bg-card p-5 shadow-sm"
            >
                <h2 class="text-base font-semibold text-card-foreground">
                    Order Status
                </h2>
                <p class="text-sm text-muted-foreground">
                    Current delivery pipeline.
                </p>
                <div class="mt-6 space-y-5">
                    <div v-for="status in orderStatuses" :key="status.value">
                        <div
                            class="mb-2 flex items-center justify-between gap-4 text-sm"
                        >
                            <span class="font-medium text-card-foreground">{{
                                status.label
                            }}</span>
                            <span class="text-muted-foreground">{{
                                formatNumber(status.count)
                            }}</span>
                        </div>
                        <div
                            class="h-2.5 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                :class="['h-full rounded-full', status.class]"
                                :style="{
                                    width: `${totalOrderStatuses ? (status.count / totalOrderStatuses) * 100 : 0}%`,
                                }"
                            />
                        </div>
                    </div>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <div class="rounded-md border border-border p-3">
                        <p class="text-xs text-muted-foreground">Products</p>
                        <p
                            class="mt-1 text-xl font-semibold text-card-foreground"
                        >
                            {{ formatNumber(summary.products) }}
                        </p>
                    </div>
                    <div class="rounded-md border border-border p-3">
                        <p class="text-xs text-muted-foreground">Active</p>
                        <p
                            class="mt-1 text-xl font-semibold text-card-foreground"
                        >
                            {{ activeProductRate }}%
                        </p>
                    </div>
                </div>
            </article>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <article
                class="rounded-lg border border-border bg-card p-5 shadow-sm"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="grid size-10 place-items-center rounded-md bg-cyan-50 text-cyan-700 dark:bg-cyan-500/10 dark:text-cyan-300"
                    >
                        <Users class="size-5" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Customers</p>
                        <p class="text-xl font-semibold text-card-foreground">
                            {{ formatNumber(summary.customers) }}
                        </p>
                    </div>
                </div>
            </article>
            <article
                class="rounded-lg border border-border bg-card p-5 shadow-sm"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="grid size-10 place-items-center rounded-md bg-fuchsia-50 text-fuchsia-700 dark:bg-fuchsia-500/10 dark:text-fuchsia-300"
                    >
                        <Truck class="size-5" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Suppliers</p>
                        <p class="text-xl font-semibold text-card-foreground">
                            {{ formatNumber(summary.suppliers) }}
                        </p>
                    </div>
                </div>
            </article>
            <article
                class="rounded-lg border border-border bg-card p-5 shadow-sm"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="grid size-10 place-items-center rounded-md bg-lime-50 text-lime-700 dark:bg-lime-500/10 dark:text-lime-300"
                    >
                        <Boxes class="size-5" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Stock Value</p>
                        <p class="text-xl font-semibold text-card-foreground">
                            {{ money(summary.stock_value) }}
                        </p>
                    </div>
                </div>
            </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.35fr_1fr]">
            <article
                class="overflow-hidden rounded-lg border border-border bg-card shadow-sm"
            >
                <div
                    class="flex items-center justify-between gap-4 border-b border-border p-5"
                >
                    <div>
                        <h2
                            class="text-base font-semibold text-card-foreground"
                        >
                            Recent Orders
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Latest customer orders and payment state.
                        </p>
                    </div>
                    <Link
                        href="/orders"
                        class="inline-flex items-center gap-1 text-sm font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-300"
                    >
                        View all
                        <ArrowUpRight class="size-4" />
                    </Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead
                            class="bg-muted/60 text-xs text-muted-foreground uppercase"
                        >
                            <tr>
                                <th class="px-5 py-3 font-medium">Order</th>
                                <th class="px-5 py-3 font-medium">Customer</th>
                                <th class="px-5 py-3 font-medium">Date</th>
                                <th class="px-5 py-3 font-medium">Amount</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="hover:bg-muted/40"
                            >
                                <td
                                    class="px-5 py-4 font-medium text-card-foreground"
                                >
                                    {{ order.order_number }}
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-medium text-card-foreground">
                                        {{ order.customer_name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ order.customer_phone ?? 'No phone' }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-muted-foreground">
                                    {{ formatDate(order.order_date) }}
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-medium text-card-foreground">
                                        {{ money(order.total_amount) }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Due {{ money(order.due_amount) }}
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            :class="[
                                                'rounded-full px-2.5 py-1 text-xs font-medium ring-1',
                                                orderStatusClass(
                                                    order.order_status,
                                                ),
                                            ]"
                                        >
                                            {{
                                                orderStatusLabel(
                                                    order.order_status,
                                                )
                                            }}
                                        </span>
                                        <span
                                            :class="[
                                                'rounded-full px-2.5 py-1 text-xs font-medium ring-1',
                                                paymentStatusClass(
                                                    order.payment_status,
                                                ),
                                            ]"
                                        >
                                            {{
                                                paymentStatusLabel(
                                                    order.payment_status,
                                                )
                                            }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="recentOrders.length === 0">
                                <td
                                    colspan="5"
                                    class="px-5 py-10 text-center text-muted-foreground"
                                >
                                    No orders found yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>

            <article
                class="rounded-lg border border-border bg-card p-5 shadow-sm"
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2
                            class="text-base font-semibold text-card-foreground"
                        >
                            Low Stock Products
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Items that need stock attention first.
                        </p>
                    </div>
                    <AlertTriangle class="size-5 text-amber-500" />
                </div>
                <div class="mt-5 space-y-4">
                    <div
                        v-for="product in lowStockProducts"
                        :key="product.id"
                        class="rounded-md border border-border p-3"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p
                                    class="truncate text-sm font-medium text-card-foreground"
                                >
                                    {{ product.name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ product.sku ?? 'No SKU' }}
                                </p>
                            </div>
                            <p
                                class="text-sm font-semibold whitespace-nowrap text-card-foreground"
                            >
                                {{ formatNumber(product.stock_quantity) }}
                                {{ product.unit ?? '' }}
                            </p>
                        </div>
                        <div
                            class="mt-3 h-2 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-amber-500"
                                :style="{
                                    width: `${Math.min(100, product.minimum_order_quantity ? (product.stock_quantity / product.minimum_order_quantity) * 100 : 100)}%`,
                                }"
                            />
                        </div>
                        <p class="mt-2 text-xs text-muted-foreground">
                            Minimum order
                            {{ formatNumber(product.minimum_order_quantity) }}
                            {{ product.unit ?? '' }}
                        </p>
                    </div>
                    <p
                        v-if="lowStockProducts.length === 0"
                        class="py-8 text-center text-sm text-muted-foreground"
                    >
                        No product stock data found.
                    </p>
                </div>
            </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <article
                class="rounded-lg border border-border bg-card p-5 shadow-sm"
            >
                <h2 class="text-base font-semibold text-card-foreground">
                    Top Categories
                </h2>
                <p class="text-sm text-muted-foreground">
                    Product distribution by category.
                </p>
                <div class="mt-5 space-y-4">
                    <div v-for="category in topCategories" :key="category.id">
                        <div
                            class="mb-2 flex items-center justify-between gap-4 text-sm"
                        >
                            <span class="font-medium text-card-foreground">{{
                                category.name
                            }}</span>
                            <span class="text-muted-foreground">{{
                                formatNumber(category.products_count)
                            }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full bg-cyan-500"
                                :style="{
                                    width: `${(category.products_count / maxCategoryProducts) * 100}%`,
                                }"
                            />
                        </div>
                    </div>
                    <p
                        v-if="topCategories.length === 0"
                        class="py-8 text-center text-sm text-muted-foreground"
                    >
                        No categories found yet.
                    </p>
                </div>
            </article>

            <article
                class="rounded-lg border border-border bg-card p-5 shadow-sm"
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2
                            class="text-base font-semibold text-card-foreground"
                        >
                            Recent Transactions
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Latest money movement across accounts.
                        </p>
                    </div>
                    <Link
                        href="/transactions"
                        class="inline-flex items-center gap-1 text-sm font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-300"
                    >
                        View all
                        <ArrowUpRight class="size-4" />
                    </Link>
                </div>
                <div class="mt-5 space-y-3">
                    <div
                        v-for="transaction in recentTransactions"
                        :key="transaction.id"
                        class="flex items-center justify-between gap-4 rounded-md border border-border p-3"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                :class="[
                                    'grid size-9 shrink-0 place-items-center rounded-md',
                                    transaction.transaction_type === 1
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                                        : 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
                                ]"
                            >
                                <ArrowDownLeft
                                    v-if="transaction.transaction_type === 1"
                                    class="size-4"
                                />
                                <ArrowUpRight v-else class="size-4" />
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="truncate text-sm font-medium text-card-foreground"
                                >
                                    {{
                                        transaction.description ??
                                        transaction.transaction_no
                                    }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ transaction.account_name ?? 'Account' }}
                                    · {{ formatDate(transaction.date) }}
                                </p>
                            </div>
                        </div>
                        <p
                            class="text-sm font-semibold whitespace-nowrap text-card-foreground"
                        >
                            {{ money(transaction.total_amount) }}
                        </p>
                    </div>
                    <p
                        v-if="recentTransactions.length === 0"
                        class="py-8 text-center text-sm text-muted-foreground"
                    >
                        No transactions found yet.
                    </p>
                </div>
            </article>
        </section>
    </div>
</template>
