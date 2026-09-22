<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import {
    CalendarDays,
    Landmark,
    LockKeyhole,
    MoreVertical,
    Pencil,
    Plus,
    Search,
    Star,
    Trash2,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
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
import { Label } from '@/components/ui/label';
import { formatDate } from '@/lib/utils';

type BankAccount = {
    id: number;
    name: string;
    slug: string;
    account_number: string | null;
    available_balance: string;
    opening_balance: string;
    opening_balance_date: string | null;
    can_edit: number;
    is_default: number;
    created_at: string | null;
};

const { bankAccounts } = defineProps<{ bankAccounts: BankAccount[] }>();

const search = ref('');
const formOpen = ref(false);
const deleteOpen = ref(false);
const defaultConfirmOpen = ref(false);
const selectedAccount = ref<BankAccount | null>(null);
const deleting = ref(false);
const changingDefault = ref(false);

const filteredAccounts = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return bankAccounts;
    }

    return bankAccounts.filter((account) =>
        [account.name, account.slug, account.account_number]
            .filter(Boolean)
            .some((value) => value!.toLowerCase().includes(query)),
    );
});

const formatBalance = (balance: string) =>
    new Intl.NumberFormat(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(balance));

const openCreate = () => {
    selectedAccount.value = null;
    formOpen.value = true;
};

const openEdit = (account: BankAccount) => {
    if (!account.can_edit) {
        return;
    }

    selectedAccount.value = account;
    formOpen.value = true;
};

const openDelete = (account: BankAccount) => {
    if (!account.can_edit) {
        return;
    }

    selectedAccount.value = account;
    deleteOpen.value = true;
};

const openDefaultConfirmation = (account: BankAccount) => {
    selectedAccount.value = account;
    defaultConfirmOpen.value = true;
};

const toggleDefault = () => {
    if (!selectedAccount.value) {
        return;
    }

    changingDefault.value = true;
    router.post(
        `/bank-accounts/${selectedAccount.value.id}/toggle-default`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                defaultConfirmOpen.value = false;
                selectedAccount.value = null;
            },
            onFinish: () => (changingDefault.value = false),
        },
    );
};

const deleteAccount = () => {
    if (!selectedAccount.value) {
        return;
    }

    deleting.value = true;
    router.delete(`/bank-accounts/${selectedAccount.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteOpen.value = false;
            selectedAccount.value = null;
        },
        onFinish: () => (deleting.value = false),
    });
};

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Bank Accounts', href: '/bank-accounts' }],
    },
});
</script>

<template>
    <Head title="Bank Accounts" />

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
                    placeholder="Search bank accounts"
                    aria-label="Search bank accounts"
                />
            </div>
            <Button class="shrink-0" @click="openCreate">
                <Plus class="size-4" />
                Add account
            </Button>
        </div>

        

        <div class="overflow-hidden rounded-md border">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="w-16 px-4 py-3 font-medium">SL</th>
                            <th class="px-4 py-3 font-medium">Account</th>
                            <th class="px-4 py-3 font-medium">
                                Account number
                            </th>
                            <th class="px-4 py-3 text-right font-medium">
                                Opening balance
                            </th>
                            <th class="px-4 py-3 text-right font-medium">
                                Available balance
                            </th>
                            <th class="px-4 py-3 font-medium">Default</th>
                            <th class="w-20 px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="(account, index) in filteredAccounts"
                            :key="account.id"
                            class="hover:bg-muted/30"
                        >
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ index + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-9 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground"
                                    >
                                        <Landmark class="size-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">{{
                                                account.name
                                            }}</span>
                                            <LockKeyhole
                                                v-if="!account.can_edit"
                                                class="size-3.5 text-muted-foreground"
                                                aria-label="Protected account"
                                            />
                                        </div>
                                        <div
                                            class="flex items-center gap-1 text-xs text-muted-foreground"
                                        >
                                            <CalendarDays class="size-3" />
                                            {{
                                                formatDate(
                                                    account.opening_balance_date,
                                                ) || 'No opening date'
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ account.account_number || 'Not provided' }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">
                                {{ formatBalance(account.opening_balance) }}
                            </td>
                            <td
                                class="px-4 py-3 text-right font-medium tabular-nums"
                            >
                                {{ formatBalance(account.available_balance) }}
                            </td>
                            <td class="px-4 py-3">
                                <Button
                                    size="sm"
                                    :variant="
                                        account.is_default
                                            ? 'outline'
                                            : 'default'
                                    "
                                    class="h-7 px-2.5 text-xs"
                                    @click="openDefaultConfirmation(account)"
                                >
                                    <Star class="size-3.5" />
                                    {{
                                        account.is_default
                                            ? 'Default'
                                            : 'Set default'
                                    }}
                                </Button>
                            </td>
                            <td class="px-4 py-3">
                                <DropdownMenu v-if="account.can_edit">
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="ml-auto flex"
                                            title="Bank account actions"
                                        >
                                            <MoreVertical class="size-4" />
                                            <span class="sr-only"
                                                >Bank account actions</span
                                            >
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            @click="openEdit(account)"
                                        >
                                            <Pencil class="size-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            variant="destructive"
                                            @click="openDelete(account)"
                                        >
                                            <Trash2 class="size-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                                <LockKeyhole
                                    v-else
                                    class="ml-auto size-4 text-muted-foreground"
                                    aria-label="Protected account"
                                />
                            </td>
                        </tr>
                        <tr v-if="filteredAccounts.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <Landmark class="mx-auto mb-3 size-8" />
                                No bank accounts found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <Dialog v-model:open="formOpen">
        <DialogContent
            class="max-h-[calc(100vh-2rem)] gap-5 overflow-y-auto p-6"
            style="width: min(620px, calc(100vw - 2rem)); max-width: 620px"
        >
            <DialogHeader class="gap-1.5 pr-6">
                <DialogTitle
                    >{ selectedAccount ? 'Edit bank account' : 'Add bank
                    account' }</DialogTitle
                >
                <DialogDescription>
                    Enter the account details and opening balance.
                </DialogDescription>
            </DialogHeader>

            <Form
                :key="selectedAccount?.id ?? 'create'"
                method="post"
                :action="
                    selectedAccount
                        ? `/bank-accounts/${selectedAccount.id}`
                        : '/bank-accounts'
                "
                class="grid gap-5 [&_input]:focus-visible:ring-1 [&_input]:focus-visible:ring-ring/20"
                :reset-on-success="!selectedAccount"
                v-slot="{ errors, processing }"
                @success="formOpen = false"
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="bank_account_name">Name</Label>
                        <Input
                            id="bank_account_name"
                            name="name"
                            :default-value="selectedAccount?.name"
                            placeholder="e.g. City Bank"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="bank_account_number">Account number</Label>
                        <Input
                            id="bank_account_number"
                            name="account_number"
                            :default-value="
                                selectedAccount?.account_number ?? ''
                            "
                            placeholder="Optional"
                        />
                        <InputError :message="errors.account_number" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="bank_account_opening_balance"
                            >Opening balance</Label
                        >
                        <Input
                            id="bank_account_opening_balance"
                            type="number"
                            name="opening_balance"
                            min="0"
                            step="0.01"
                            :default-value="
                                selectedAccount?.opening_balance ?? '0.00'
                            "
                            required
                        />
                        <InputError :message="errors.opening_balance" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="bank_account_opening_balance_date"
                            >Opening balance date</Label
                        >
                        <Input
                            id="bank_account_opening_balance_date"
                            type="date"
                            name="opening_balance_date"
                            :default-value="
                                selectedAccount?.opening_balance_date ?? ''
                            "
                        />
                        <InputError :message="errors.opening_balance_date" />
                    </div>
                </div>

                <label
                    class="flex cursor-pointer items-start gap-3 rounded-md border p-3"
                >
                    <input type="hidden" name="is_default" value="0" />
                    <input
                        type="checkbox"
                        name="is_default"
                        value="1"
                        :checked="Boolean(selectedAccount?.is_default)"
                        class="mt-0.5 size-4 accent-primary"
                    />
                    <span>
                        <span class="block text-sm font-medium"
                            >Default account</span
                        >
                        <span class="block text-xs text-muted-foreground"
                            >Use this account as the default for new
                            transactions.</span
                        >
                    </span>
                </label>
                <InputError :message="errors.is_default" />

                <DialogFooter class="border-t pt-4">
                    <Button
                        type="button"
                        variant="outline"
                        @click="formOpen = false"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{ selectedAccount ? 'Update' : 'Create account' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="defaultConfirmOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Update default account</DialogTitle>
                <DialogDescription>
                    {{
                        selectedAccount?.is_default
                            ? `Remove ${selectedAccount.name} as the default account?`
                            : `Use ${selectedAccount?.name} as the default account?`
                    }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="defaultConfirmOpen = false">
                    Cancel
                </Button>
                <Button :disabled="changingDefault" @click="toggleDefault">
                    Confirm
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="deleteOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete bank account</DialogTitle>
                <DialogDescription>
                    Delete {{ selectedAccount?.name }}? It will no longer appear
                    in the bank account list.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="deleteOpen = false">
                    Cancel
                </Button>
                <Button
                    variant="destructive"
                    :disabled="deleting"
                    @click="deleteAccount"
                >
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
