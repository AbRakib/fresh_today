<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    ArrowDownLeft,
    ArrowUpRight,
    CalendarDays,
    CircleDollarSign,
    Landmark,
    ReceiptText,
    Search,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { Input } from '@/components/ui/input';
import { formatDate } from '@/lib/utils';

type Transaction = {
    id: number;
    transaction_no: string;
    date: string | null;
    account_name: string | null;
    account_number: string | null;
    payment_type: number;
    transaction_type: number;
    reference_type: string | null;
    reference_description: string | null;
    description: string | null;
    total_amount: string;
    reviewed: number;
    created_at: string | null;
};

const { transactions } = defineProps<{ transactions: Transaction[] }>();

const search = ref('');

const money = (value: string | number) =>
    Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

const transactionTypeLabel = (type: number) =>
    type === 0 ? 'Withdraw' : 'Deposit';

const paymentTypeLabel = (type: number) => (type === 1 ? 'Paid' : 'Unpaid');

const referenceLabel = (referenceType: string | null) => {
    if (!referenceType) {
        return 'Manual';
    }

    return referenceType.split('\\').pop() || referenceType;
};

const filteredTransactions = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return transactions;
    }

    return transactions.filter((transaction) =>
        [
            transaction.transaction_no,
            transaction.account_name,
            transaction.account_number,
            transaction.reference_description,
            transaction.description,
            transactionTypeLabel(transaction.transaction_type),
            paymentTypeLabel(transaction.payment_type),
            referenceLabel(transaction.reference_type),
        ]
            .filter(Boolean)
            .some((value) => value!.toLowerCase().includes(query)),
    );
});

const totalDeposits = computed(() =>
    transactions
        .filter((transaction) => transaction.transaction_type === 1)
        .reduce(
            (total, transaction) =>
                total + Number(transaction.total_amount || 0),
            0,
        ),
);

const totalWithdraws = computed(() =>
    transactions
        .filter((transaction) => transaction.transaction_type === 0)
        .reduce(
            (total, transaction) =>
                total + Number(transaction.total_amount || 0),
            0,
        ),
);

const netAmount = computed(() => totalDeposits.value - totalWithdraws.value);

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Transactions', href: '/transactions' }],
    },
});
</script>

<template>
    <Head title="Transactions" />

    <div class="flex h-full flex-1 flex-col gap-2 p-4 md:p-6">
        <div class="grid gap-3 sm:grid-cols-3 mb-3">
            <div class="rounded-md border p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm text-muted-foreground">
                            Deposits
                        </div>
                        <div class="mt-1 text-2xl font-semibold tabular-nums">
                            {{ money(totalDeposits) }}
                        </div>
                    </div>
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-md bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300"
                    >
                        <ArrowDownLeft class="size-5" />
                    </div>
                </div>
            </div>
            <div class="rounded-md border p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm text-muted-foreground">
                            Withdraws
                        </div>
                        <div class="mt-1 text-2xl font-semibold tabular-nums">
                            {{ money(totalWithdraws) }}
                        </div>
                    </div>
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-md bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300"
                    >
                        <ArrowUpRight class="size-5" />
                    </div>
                </div>
            </div>
            <div class="rounded-md border p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm text-muted-foreground">
                            Net activity
                        </div>
                        <div class="mt-1 text-2xl font-semibold tabular-nums">
                            {{ money(netAmount) }}
                        </div>
                    </div>
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground"
                    >
                        <CircleDollarSign class="size-5" />
                    </div>
                </div>
            </div>
        </div>

        <div class="relative max-w-sm">
            <Search
                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
            />
            <Input
                v-model="search"
                class="pl-9"
                placeholder="Search transactions"
                aria-label="Search transactions"
            />
        </div>

        <div class="overflow-hidden rounded-md border">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] table-fixed text-sm">
                    <colgroup>
                        <col class="w-[6%]" />
                        <col class="w-[18%]" />
                        <col class="w-[22%]" />
                        <col class="w-[22%]" />
                        <col class="w-[10%]" />
                        <col class="w-[12%]" />
                        <col class="w-[10%]" />
                    </colgroup>
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="w-16 px-4 py-3 font-medium">SL</th>
                            <th class="px-4 py-3 font-medium">Transaction</th>
                            <th class="px-4 py-3 font-medium">Account</th>
                            <th class="px-4 py-3 font-medium">Reference</th>
                            <th class="px-4 py-3 font-medium">Type</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Amount
                            </th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y [&_td]:align-middle">
                        <tr
                            v-for="(transaction, index) in filteredTransactions"
                            :key="transaction.id"
                            class="hover:bg-muted/30"
                        >
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ index + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="truncate font-medium">
                                    {{ transaction.transaction_no }}
                                </div>
                                <div
                                    class="mt-1 flex items-center gap-1 text-xs text-muted-foreground"
                                >
                                    <CalendarDays class="size-3" />
                                    {{
                                        formatDate(transaction.date) ||
                                        'No date'
                                    }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-9 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground"
                                    >
                                        <Landmark class="size-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate font-medium">
                                            {{
                                                transaction.account_name ||
                                                'No account'
                                            }}
                                        </div>
                                        <div
                                            class="truncate text-xs text-muted-foreground"
                                        >
                                            {{
                                                transaction.account_number ||
                                                'No account number'
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="truncate font-medium">
                                    {{
                                        transaction.reference_description ||
                                        'No reference'
                                    }}
                                </div>
                                <div
                                    class="mt-1 truncate text-xs text-muted-foreground"
                                >
                                    {{ referenceLabel(transaction.reference_type) }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span
                                    class="inline-flex rounded-sm px-1.5 py-0.5 text-xs font-medium"
                                    :class="
                                        transaction.transaction_type === 1
                                            ? 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300'
                                            : 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300'
                                    "
                                >
                                    {{
                                        transactionTypeLabel(
                                            transaction.transaction_type,
                                        )
                                    }}
                                </span>
                            </td>
                            <td
                                class="whitespace-nowrap px-4 py-3 text-right font-medium tabular-nums"
                            >
                                {{ money(transaction.total_amount) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex flex-col items-start gap-1">
                                    <span
                                        class="inline-flex rounded-sm px-1.5 py-0.5 text-xs font-medium"
                                        :class="
                                            transaction.payment_type === 1
                                                ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                                                : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'
                                        "
                                    >
                                        {{
                                            paymentTypeLabel(
                                                transaction.payment_type,
                                            )
                                        }}
                                    </span>
                                    <span
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{
                                            transaction.reviewed
                                                ? 'Reviewed'
                                                : 'Not reviewed'
                                        }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredTransactions.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <ReceiptText class="mx-auto mb-3 size-8" />
                                No transactions found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
