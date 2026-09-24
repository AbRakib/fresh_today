<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ChartNoAxesCombined,
    ChevronDown,
    CircleDollarSign,
    Download,
    ReceiptText,
    Scale,
} from '@lucide/vue';
import { ref } from 'vue';
import { Input } from '@/components/ui/input';
import { useCurrency } from '@/composables/useCurrency';
import { formatDate } from '@/lib/utils';

type ProfitLossOrder = {
    id: number;
    order_number: string;
    customer_name: string | null;
    order_date: string | null;
    subtotal: number;
    discount_amount: number;
    delivery_charge: number;
    total_amount: number;
    cost_amount: number;
    product_profit: number;
    profit_amount: number;
};

type Summary = {
    order_count: number;
    subtotal: number;
    discount_amount: number;
    delivery_charge: number;
    total_amount: number;
    cost_amount: number;
    product_profit: number;
    profit_amount: number;
};

const props = defineProps<{
    filters: {
        period: string;
        start_date: string | null;
        end_date: string | null;
    };
    orders: ProfitLossOrder[];
    summary: Summary;
}>();

const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const period = ref(props.filters.period || 'today');
const { money } = useCurrency();

const applyFilters = () => {
    if (period.value === 'date_range' && (!startDate.value || !endDate.value)) {
        return;
    }

    router.get(
        '/reports/profit-loss',
        {
            period: period.value,
            start_date:
                period.value === 'date_range'
                    ? startDate.value || undefined
                    : undefined,
            end_date:
                period.value === 'date_range'
                    ? endDate.value || undefined
                    : undefined,
        },
        { preserveState: true, replace: true },
    );
};

const changePeriod = () => {
    if (period.value !== 'date_range') {
        applyFilters();
    }
};

const clearFilters = () => {
    period.value = 'today';
    startDate.value = '';
    endDate.value = '';
    applyFilters();
};

const downloadPdf = () => {
    const params = new URLSearchParams({ period: period.value });

    if (period.value === 'date_range') {
        if (!startDate.value || !endDate.value) return;

        params.set('start_date', startDate.value);
        params.set('end_date', endDate.value);
    }

    window.location.href = `/reports/profit-loss/pdf?${params.toString()}`;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Profit Loss Report', href: '/reports/profit-loss' },
        ],
    },
});
</script>

<template>
    <Head title="Profit Loss Report" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
        <div>
            <div>
                <h1 class="text-xl font-semibold">Profit Loss Report</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Analyze sales, expenses, costs, and net profit or loss by
                    date range.
                </p>
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-md border p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm text-muted-foreground">
                            Total Sales
                        </div>
                        <div
                            class="mt-1 text-2xl font-semibold text-blue-600 tabular-nums dark:text-blue-400"
                        >
                            {{ money(summary.total_amount) }}
                        </div>
                    </div>
                    <div
                        class="flex size-10 items-center justify-center rounded-md bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                    >
                        <ReceiptText class="size-5" />
                    </div>
                </div>
            </div>
            <div class="rounded-md border p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm text-muted-foreground">
                            Product Cost
                        </div>
                        <div
                            class="mt-1 text-2xl font-semibold text-red-600 tabular-nums dark:text-red-400"
                        >
                            {{ money(summary.cost_amount) }}
                        </div>
                    </div>
                    <div
                        class="flex size-10 items-center justify-center rounded-md bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300"
                    >
                        <Scale class="size-5" />
                    </div>
                </div>
            </div>
            <div class="rounded-md border p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm text-muted-foreground">
                            Product Profit
                        </div>
                        <div class="mt-1 text-2xl font-semibold tabular-nums">
                            {{ money(summary.product_profit) }}
                        </div>
                    </div>
                    <div
                        class="flex size-10 items-center justify-center rounded-md bg-muted text-muted-foreground"
                    >
                        <ChartNoAxesCombined class="size-5" />
                    </div>
                </div>
            </div>
            <div class="rounded-md border p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm text-muted-foreground">
                            Net Profit
                        </div>
                        <div class="mt-1 text-2xl font-semibold tabular-nums">
                            {{ money(summary.profit_amount) }}
                        </div>
                    </div>
                    <div
                        class="flex size-10 items-center justify-center rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                    >
                        <CircleDollarSign class="size-5" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-2">
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="relative w-full sm:w-44">
                        <select
                            id="period"
                            v-model="period"
                            aria-label="Period"
                            class="h-10 w-full appearance-none rounded-md border border-input bg-background py-1 pr-9 pl-3 text-sm"
                            @change="changePeriod"
                        >
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="this_month">This month</option>
                            <option value="last_month">Last month</option>
                            <option value="this_year">This year</option>
                            <option value="date_range">Date range</option>
                        </select>
                        <ChevronDown
                            class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-muted-foreground"
                            aria-hidden="true"
                        />
                    </div>
                    <div v-if="period === 'date_range'" class="grid gap-1.5">
                        <label class="text-sm font-medium" for="start_date">
                            Start date
                        </label>
                        <Input
                            id="start_date"
                            v-model="startDate"
                            type="date"
                            class="w-full sm:w-44"
                            @change="applyFilters"
                        />
                    </div>
                    <div v-if="period === 'date_range'" class="grid gap-1.5">
                        <label class="text-sm font-medium" for="end_date">
                            End date
                        </label>
                        <Input
                            id="end_date"
                            v-model="endDate"
                            type="date"
                            class="w-full sm:w-44"
                            @change="applyFilters"
                        />
                    </div>
                    <button
                        v-if="period === 'date_range'"
                        type="button"
                        class="h-10 rounded-md border px-4 text-sm font-medium hover:bg-muted"
                        @click="clearFilters"
                    >
                        Clear
                    </button>
                </div>
                <button
                    type="button"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:pointer-events-none disabled:opacity-50"
                    :disabled="
                        period === 'date_range' && (!startDate || !endDate)
                    "
                    @click="downloadPdf"
                >
                    <Download class="size-4" />
                    Download PDF
                </button>
            </div>

            <div class="overflow-hidden rounded-md border">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1120px] table-fixed text-sm">
                        <colgroup>
                            <col class="w-[6%]" />
                            <col class="w-[18%]" />
                            <col class="w-[14%]" />
                            <col class="w-[12%]" />
                            <col class="w-[12%]" />
                            <col class="w-[12%]" />
                            <col class="w-[12%]" />
                            <col class="w-[14%]" />
                        </colgroup>
                        <thead class="border-b bg-muted/50 text-left">
                            <tr>
                                <th class="px-4 py-3 font-medium">SL</th>
                                <th class="px-4 py-3 font-medium">Order</th>
                                <th class="px-4 py-3 text-center font-medium">
                                    Date
                                </th>
                                <th class="px-4 py-3 text-center font-medium">
                                    Sales
                                </th>
                                <th class="px-4 py-3 text-center font-medium">
                                    Discount
                                </th>
                                <th class="px-4 py-3 text-center font-medium">
                                    Cost
                                </th>
                                <th class="px-4 py-3 text-center font-medium">
                                    Delivery
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Profit / Loss
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="(order, index) in orders"
                                :key="order.id"
                                class="hover:bg-muted/30"
                            >
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ index + 1 }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="truncate font-medium">
                                        {{ order.order_number }}
                                    </div>
                                    <div
                                        class="truncate text-xs text-muted-foreground"
                                    >
                                        {{
                                            order.customer_name || 'No customer'
                                        }}
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-3 text-center text-muted-foreground"
                                >
                                    {{
                                        formatDate(order.order_date) ||
                                        'No date'
                                    }}
                                </td>
                                <td
                                    class="px-4 py-3 text-center text-blue-600 tabular-nums dark:text-blue-400"
                                >
                                    {{ money(order.total_amount) }}
                                </td>
                                <td class="px-4 py-3 text-center tabular-nums">
                                    {{ money(order.discount_amount) }}
                                </td>
                                <td
                                    class="px-4 py-3 text-center text-red-600 tabular-nums dark:text-red-400"
                                >
                                    {{ money(order.cost_amount) }}
                                </td>
                                <td class="px-4 py-3 text-center tabular-nums">
                                    {{ money(order.delivery_charge) }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-medium tabular-nums"
                                    :class="
                                        order.profit_amount < 0
                                            ? 'text-red-600 dark:text-red-400'
                                            : 'text-emerald-700 dark:text-emerald-300'
                                    "
                                >
                                    {{ money(order.profit_amount) }}
                                </td>
                            </tr>
                            <tr v-if="orders.length === 0">
                                <td
                                    colspan="8"
                                    class="px-4 py-10 text-center text-muted-foreground"
                                >
                                    No profit loss records found.
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t bg-muted/30 font-medium">
                            <tr>
                                <td class="px-4 py-3" colspan="3">
                                    {{ summary.order_count }} order(s)
                                </td>
                                <td
                                    class="px-4 py-3 text-center text-blue-600 tabular-nums dark:text-blue-400"
                                >
                                    {{ money(summary.total_amount) }}
                                </td>
                                <td class="px-4 py-3 text-center tabular-nums">
                                    {{ money(summary.discount_amount) }}
                                </td>
                                <td
                                    class="px-4 py-3 text-center text-red-600 tabular-nums dark:text-red-400"
                                >
                                    {{ money(summary.cost_amount) }}
                                </td>
                                <td class="px-4 py-3 text-center tabular-nums">
                                    {{ money(summary.delivery_charge) }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums">
                                    {{ money(summary.profit_amount) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
