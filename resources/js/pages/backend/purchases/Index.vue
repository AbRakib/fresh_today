<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    MoreVertical,
    PackageCheck,
    Pencil,
    Plus,
    Search,
    Trash2,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { useCurrency } from '@/composables/useCurrency';
import { formatDate } from '@/lib/utils';

type Purchase = {
    id: number;
    purchase_number: string;
    supplier_name: string | null;
    supplier_photo_url: string | null;
    supplier_phone: string | null;
    total_product: number;
    subtotal: string;
    paid_amount: string;
    due_amount: string;
    purchase_date: string | null;
    created_by_name: string | null;
    note: string | null;
    receive_status: number;
    payment_status: number;
    details: PurchaseDetail[];
};

type PurchaseDetail = {
    id: number;
    product_name: string;
    product_sku: string | null;
    purchase_qty: number;
    purchase_price: string;
    total_amount: string;
    expire_date: string | null;
};

type BankAccount = {
    id: number;
    name: string;
    account_number: string | null;
    available_balance: string;
    is_default: number;
};

const { purchases, bankAccounts } = defineProps<{
    purchases: Purchase[];
    bankAccounts: BankAccount[];
}>();

const search = ref('');
const deleteOpen = ref(false);
const selectedPurchase = ref<Purchase | null>(null);
const deleting = ref(false);
const paymentOpen = ref(false);
const paymentPurchase = ref<Purchase | null>(null);
const paymentForm = useForm({
    account_id: '',
    amount: '',
    note: '',
});
const receiveOpen = ref(false);
const receivePurchaseItem = ref<Purchase | null>(null);
const receivingPurchaseId = ref<number | null>(null);
const { money } = useCurrency();

const filteredPurchases = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return purchases;
    }

    return purchases.filter((purchase) =>
        [
            purchase.purchase_number,
            purchase.supplier_name,
            purchase.supplier_phone,
            purchase.note,
            receiveStatusLabel(purchase.receive_status),
            paymentStatusLabel(purchase.payment_status),
        ]
            .filter(Boolean)
            .some((value) => value!.toLowerCase().includes(query)),
    );
});

const defaultBankAccount = computed(
    () =>
        bankAccounts.find((account) => account.is_default === 1) ??
        bankAccounts[0],
);

const selectedPaymentAccount = computed(() =>
    bankAccounts.find(
        (account) => String(account.id) === String(paymentForm.account_id),
    ),
);

const initials = (name: string | null) =>
    (name || 'S')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join('');

const receiveStatusLabel = (status: number) =>
    status === 1 ? 'Received' : 'Not Received';

const paymentStatusLabel = (status: number) => {
    if (status === 1) {
        return 'Paid';
    }

    if (status === 2) {
        return 'Partial Paid';
    }

    return 'Unpaid';
};

const openDelete = (purchase: Purchase) => {
    selectedPurchase.value = purchase;
    deleteOpen.value = true;
};

const openPayment = (purchase: Purchase) => {
    paymentPurchase.value = purchase;
    paymentForm.reset();
    paymentForm.account_id = defaultBankAccount.value
        ? String(defaultBankAccount.value.id)
        : '';
    paymentForm.amount = Number(purchase.due_amount || 0).toFixed(2);
    paymentForm.note = '';
    paymentForm.clearErrors();
    paymentOpen.value = true;
};

const submitPayment = () => {
    if (!paymentPurchase.value) {
        return;
    }

    paymentForm.post(`/purchases/${paymentPurchase.value.id}/payment`, {
        preserveScroll: true,
        onSuccess: () => {
            paymentOpen.value = false;
            paymentPurchase.value = null;
            paymentForm.reset();
        },
    });
};

const openReceive = (purchase: Purchase) => {
    receivePurchaseItem.value = purchase;
    receiveOpen.value = true;
};

const confirmReceive = () => {
    if (!receivePurchaseItem.value) {
        return;
    }

    receivingPurchaseId.value = receivePurchaseItem.value.id;

    router.post(
        `/purchases/${receivePurchaseItem.value.id}/receive`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                receiveOpen.value = false;
                receivePurchaseItem.value = null;
            },
            onFinish: () => {
                receivingPurchaseId.value = null;
            },
        },
    );
};

const deletePurchase = () => {
    if (!selectedPurchase.value) {
        return;
    }

    deleting.value = true;
    router.delete(`/purchases/${selectedPurchase.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteOpen.value = false;
            selectedPurchase.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
};

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Purchases', href: '/purchases' }],
    },
});
</script>

<template>
    <Head title="Purchases" />

    <div class="flex h-full flex-1 flex-col gap-2 p-4 md:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="relative max-w-sm">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    class="pl-9"
                    placeholder="Search purchases"
                    aria-label="Search purchases"
                />
            </div>
            <Button class="shrink-0" @click="router.visit('/purchases/create')">
                <Plus class="size-4" />
                Add purchase
            </Button>
        </div>

        <div class="overflow-hidden rounded-md border">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="w-16 px-4 py-3 font-medium">SL</th>
                            <th class="px-4 py-3 font-medium">Purchase</th>
                            <th class="px-4 py-3 font-medium">Supplier</th>
                            <th class="px-4 py-3 text-center font-medium">
                                Items
                            </th>
                            <th class="px-4 py-3 text-center font-medium">
                                Subtotal
                            </th>
                            <th class="px-4 py-3 text-center font-medium">
                                Status
                            </th>
                            <th class="px-4 py-3 font-medium">Date</th>
                            <th class="w-24 px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="(purchase, index) in filteredPurchases"
                            :key="purchase.id"
                            class="hover:bg-muted/30"
                        >
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ index + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">
                                    {{ purchase.purchase_number }}
                                </div>
                                <span
                                    class="mt-1 inline-flex rounded-sm px-1.5 py-0.5 text-[11px] font-normal"
                                    :class="
                                        purchase.receive_status === 1
                                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                                            : 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300'
                                    "
                                >
                                    {{
                                        receiveStatusLabel(
                                            purchase.receive_status,
                                        )
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-muted text-xs font-medium text-muted-foreground"
                                    >
                                        <img
                                            v-if="purchase.supplier_photo_url"
                                            :src="purchase.supplier_photo_url"
                                            :alt="
                                                purchase.supplier_name ??
                                                'Supplier'
                                            "
                                            class="size-full object-cover"
                                        />
                                        <span v-else>
                                            {{
                                                initials(purchase.supplier_name)
                                            }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate font-medium">
                                            {{
                                                purchase.supplier_name ||
                                                'No supplier'
                                            }}
                                        </div>
                                        <div
                                            class="truncate text-xs text-muted-foreground"
                                        >
                                            {{
                                                purchase.supplier_phone ||
                                                'No phone'
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="px-4 py-3 text-center text-muted-foreground"
                            >
                                {{ purchase.total_product }}
                            </td>
                            <td class="px-4 py-3 text-center font-medium">
                                {{ money(purchase.subtotal) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <span
                                        class="inline-flex rounded-sm px-1.5 py-0.5 text-xs font-medium"
                                        :class="{
                                            'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300':
                                                purchase.payment_status === 1,
                                            'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300':
                                                purchase.payment_status === 2,
                                            'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300':
                                                purchase.payment_status === 0,
                                        }"
                                    >
                                        {{
                                            paymentStatusLabel(
                                                purchase.payment_status,
                                            )
                                        }}
                                    </span>
                                    <button
                                        v-if="purchase.payment_status !== 1"
                                        type="button"
                                        class="text-xs font-medium text-blue-600 underline-offset-4 hover:text-blue-700 hover:underline dark:text-blue-400 dark:hover:text-blue-300"
                                        @click="openPayment(purchase)"
                                    >
                                        Make Payment
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-muted-foreground">
                                    {{
                                        formatDate(purchase.purchase_date) ||
                                        'Not available'
                                    }}
                                </div>
                                <div class="mt-1 text-xs text-muted-foreground">
                                    Created By:
                                    {{ purchase.created_by_name || 'Unknown' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="ml-auto flex"
                                            title="Purchase actions"
                                        >
                                            <MoreVertical class="size-4" />
                                            <span class="sr-only"
                                                >Purchase actions</span
                                            >
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            v-if="purchase.receive_status !== 1"
                                            :disabled="
                                                receivingPurchaseId ===
                                                purchase.id
                                            "
                                            @click="openReceive(purchase)"
                                        >
                                            <PackageCheck class="size-4" />
                                            {{
                                                receivingPurchaseId ===
                                                purchase.id
                                                    ? 'Receiving...'
                                                    : 'Receive'
                                            }}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="
                                                router.visit(
                                                    `/purchases/${purchase.id}/edit`,
                                                )
                                            "
                                        >
                                            <Pencil class="size-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            variant="destructive"
                                            @click="openDelete(purchase)"
                                        >
                                            <Trash2 class="size-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="!filteredPurchases.length">
                            <td
                                colspan="8"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                No purchases found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Dialog v-model:open="receiveOpen">
            <DialogContent class="sm:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>Receive purchase</DialogTitle>
                    <DialogDescription>
                        Review the items before receiving
                        {{ receivePurchaseItem?.purchase_number }}.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="receivePurchaseItem" class="space-y-4">
                    <div
                        class="grid gap-3 rounded-md border bg-muted/30 p-3 text-sm sm:grid-cols-3"
                    >
                        <div>
                            <div class="text-xs text-muted-foreground">
                                Supplier
                            </div>
                            <div class="font-medium">
                                {{
                                    receivePurchaseItem.supplier_name ||
                                    'No supplier'
                                }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-muted-foreground">
                                Purchase date
                            </div>
                            <div class="font-medium">
                                {{
                                    formatDate(
                                        receivePurchaseItem.purchase_date,
                                    ) || 'Not available'
                                }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-muted-foreground">
                                Subtotal
                            </div>
                            <div class="font-medium">
                                {{ money(receivePurchaseItem.subtotal) }}
                            </div>
                        </div>
                    </div>

                    <div class="max-h-80 overflow-auto rounded-md border">
                        <table class="w-full text-sm">
                            <thead
                                class="sticky top-0 border-b bg-muted text-left"
                            >
                                <tr>
                                    <th class="w-16 px-3 py-2 font-medium">
                                        SL
                                    </th>
                                    <th class="px-3 py-2 font-medium">
                                        Product Name
                                    </th>
                                    <th
                                        class="px-3 py-2 text-right font-medium"
                                    >
                                        Receive Qty
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="(
                                        detail, index
                                    ) in receivePurchaseItem.details"
                                    :key="detail.id"
                                >
                                    <td class="px-3 py-2 text-muted-foreground">
                                        {{ index + 1 }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="font-medium">
                                            {{ detail.product_name }}
                                        </div>
                                        <div
                                            v-if="detail.product_sku"
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ detail.product_sku }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        {{ detail.purchase_qty }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-sm text-muted-foreground">
                        Confirming will add these quantities to product stock.
                    </p>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        :disabled="receivingPurchaseId !== null"
                        @click="receiveOpen = false"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        :disabled="receivingPurchaseId !== null"
                        @click="confirmReceive"
                    >
                        <PackageCheck class="size-4" />
                        {{
                            receivingPurchaseId !== null
                                ? 'Receiving...'
                                : 'Confirm receive'
                        }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="paymentOpen">
            <DialogContent>
                <form class="space-y-4" @submit.prevent="submitPayment">
                    <DialogHeader>
                        <DialogTitle>Purchase payment</DialogTitle>
                        <DialogDescription>
                            Record payment for
                            {{ paymentPurchase?.purchase_number }}. Due:
                            {{ money(paymentPurchase?.due_amount ?? 0) }}
                        </DialogDescription>
                    </DialogHeader>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label
                                for="payment_account"
                                class="text-sm font-medium"
                            >
                                Account
                            </label>
                            <select
                                id="payment_account"
                                v-model="paymentForm.account_id"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-foreground/60 disabled:cursor-not-allowed disabled:opacity-50"
                                autofocus
                            >
                                <option value="" disabled>
                                    Select account
                                </option>
                                <option
                                    v-for="account in bankAccounts"
                                    :key="account.id"
                                    :value="String(account.id)"
                                >
                                    {{ account.name }}
                                    {{
                                        account.account_number
                                            ? `(${account.account_number})`
                                            : ''
                                    }}
                                </option>
                            </select>
                            <p
                                v-if="selectedPaymentAccount"
                                class="text-xs text-muted-foreground"
                            >
                                Available balance:
                                {{
                                    money(
                                        selectedPaymentAccount.available_balance,
                                    )
                                }}
                            </p>
                            <p
                                v-if="paymentForm.errors.account_id"
                                class="text-sm text-destructive"
                            >
                                {{ paymentForm.errors.account_id }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <label
                                for="payment_amount"
                                class="text-sm font-medium"
                            >
                                Payment amount
                            </label>
                            <Input
                                id="payment_amount"
                                v-model="paymentForm.amount"
                                class="focus-visible:border-foreground/60 focus-visible:ring-0"
                                type="number"
                                min="0.01"
                                step="0.01"
                                :max="paymentPurchase?.due_amount"
                                placeholder="0.00"
                            />
                            <p class="text-xs text-muted-foreground">
                                Amount cannot exceed the total due.
                            </p>
                            <p
                                v-if="paymentForm.errors.amount"
                                class="text-sm text-destructive"
                            >
                                {{ paymentForm.errors.amount }}
                            </p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="payment_note" class="text-sm font-medium">
                            Note
                        </label>
                        <textarea
                            id="payment_note"
                            v-model="paymentForm.note"
                            class="flex min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-colors outline-none placeholder:text-muted-foreground focus-visible:border-foreground/60 disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="Optional payment note"
                        />
                        <p
                            v-if="paymentForm.errors.note"
                            class="text-sm text-destructive"
                        >
                            {{ paymentForm.errors.note }}
                        </p>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="paymentOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            :disabled="paymentForm.processing"
                        >
                            {{
                                paymentForm.processing
                                    ? 'Saving...'
                                    : 'Save payment'
                            }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="deleteOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete purchase</DialogTitle>
                    <DialogDescription>
                        This will remove
                        {{ selectedPurchase?.purchase_number }} and reduce any
                        received product stock.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="deleteOpen = false"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        :disabled="deleting"
                        @click="deletePurchase"
                    >
                        {{ deleting ? 'Deleting...' : 'Delete' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
