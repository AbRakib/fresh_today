<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { MoreVertical, Pencil, Plus, Search, Trash2, Truck } from '@lucide/vue';
import { computed, ref } from 'vue';
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

type DeliveryCharge = {
    id: number;
    title: string;
    amount: string;
    status: number;
    created_at: string | null;
};

const { deliveryCharges } = defineProps<{
    deliveryCharges: DeliveryCharge[];
}>();

const search = ref('');
const formOpen = ref(false);
const deleteOpen = ref(false);
const selectedDeliveryCharge = ref<DeliveryCharge | null>(null);
const deleting = ref(false);

const filteredDeliveryCharges = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return deliveryCharges;
    }

    return deliveryCharges.filter((deliveryCharge) =>
        deliveryCharge.title.toLowerCase().includes(query),
    );
});

const formatAmount = (amount: string) =>
    new Intl.NumberFormat(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(amount));

const openCreate = () => {
    selectedDeliveryCharge.value = null;
    formOpen.value = true;
};

const openEdit = (deliveryCharge: DeliveryCharge) => {
    selectedDeliveryCharge.value = deliveryCharge;
    formOpen.value = true;
};

const openDelete = (deliveryCharge: DeliveryCharge) => {
    selectedDeliveryCharge.value = deliveryCharge;
    deleteOpen.value = true;
};

const deleteDeliveryCharge = () => {
    if (!selectedDeliveryCharge.value) {
        return;
    }

    deleting.value = true;
    router.delete(`/delivery-charges/${selectedDeliveryCharge.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteOpen.value = false;
            selectedDeliveryCharge.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
};

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Delivery Charges', href: '/delivery-charges' }],
    },
});
</script>

<template>
    <Head title="Delivery Charges" />

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
                    placeholder="Search delivery charges"
                    aria-label="Search delivery charges"
                />
            </div>
            <Button class="shrink-0" @click="openCreate">
                <Plus class="size-4" />
                Add delivery charge
            </Button>
        </div>

        <div class="overflow-hidden rounded-md border">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] table-fixed text-sm">
                    <colgroup>
                        <col class="w-[5%]" />
                        <col class="w-[43%]" />
                        <col class="w-[12%]" />
                        <col class="w-[12%]" />
                        <col class="w-[18%]" />
                        <col class="w-[10%]" />
                    </colgroup>
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">SL</th>
                            <th class="px-4 py-3 font-medium">
                                Delivery charge
                            </th>
                            <th class="px-4 py-3 font-medium">Amount</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Created</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="(
                                deliveryCharge, index
                            ) in filteredDeliveryCharges"
                            :key="deliveryCharge.id"
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
                                        <Truck class="size-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium">
                                            {{ deliveryCharge.title }}
                                        </div>
                                        <div
                                            class="truncate text-muted-foreground"
                                        >
                                            Charge #{{ deliveryCharge.id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 tabular-nums">
                                {{ formatAmount(deliveryCharge.amount) }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        deliveryCharge.status
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    {{
                                        deliveryCharge.status
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{
                                    formatDate(deliveryCharge.created_at) ||
                                    'Not available'
                                }}
                            </td>
                            <td class="px-4 py-3">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="ml-auto flex"
                                            title="Delivery charge actions"
                                        >
                                            <MoreVertical class="size-4" />
                                            <span class="sr-only"
                                                >Delivery charge actions</span
                                            >
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            @click="openEdit(deliveryCharge)"
                                        >
                                            <Pencil class="size-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            variant="destructive"
                                            @click="openDelete(deliveryCharge)"
                                        >
                                            <Trash2 class="size-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="filteredDeliveryCharges.length === 0">
                            <td
                                colspan="6"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <Truck class="mx-auto mb-3 size-8" />
                                No delivery charges found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <Dialog v-model:open="formOpen">
        <DialogContent
            class="max-h-[calc(100vh-2rem)] gap-5 overflow-x-hidden overflow-y-auto p-6"
            style="width: min(540px, calc(100vw - 2rem)); max-width: 540px"
        >
            <DialogHeader class="gap-1.5 pr-6">
                <DialogTitle>
                    {{
                        selectedDeliveryCharge
                            ? 'Edit delivery charge'
                            : 'Add delivery charge'
                    }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        selectedDeliveryCharge
                            ? 'Update the delivery charge information below.'
                            : 'Enter the new delivery charge information below.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Form
                :key="selectedDeliveryCharge?.id ?? 'create'"
                method="post"
                :action="
                    selectedDeliveryCharge
                        ? `/delivery-charges/${selectedDeliveryCharge.id}`
                        : '/delivery-charges'
                "
                class="grid min-w-0 gap-5 [&_input]:focus-visible:ring-1 [&_input]:focus-visible:ring-ring/20"
                :reset-on-success="!selectedDeliveryCharge"
                v-slot="{ errors, processing }"
                @success="formOpen = false"
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="delivery_charge_title">Title</Label>
                        <Input
                            id="delivery_charge_title"
                            name="title"
                            :default-value="selectedDeliveryCharge?.title"
                            placeholder="e.g. Inside Dhaka"
                            required
                        />
                        <div class="min-h-5">
                            <InputError :message="errors.title" />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="delivery_charge_amount">Amount</Label>
                        <Input
                            id="delivery_charge_amount"
                            type="number"
                            name="amount"
                            min="0"
                            step="0.01"
                            :default-value="
                                selectedDeliveryCharge?.amount ?? '0.00'
                            "
                            required
                        />
                        <div class="min-h-5">
                            <InputError :message="errors.amount" />
                        </div>
                    </div>
                </div>

                <DialogFooter class="border-t pt-4">
                    <Button
                        type="button"
                        variant="outline"
                        class="cursor-pointer"
                        @click="formOpen = false"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        class="cursor-pointer"
                        :disabled="processing"
                    >
                        {{ selectedDeliveryCharge ? 'Update' : 'Submit' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="deleteOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete delivery charge</DialogTitle>
                <DialogDescription>
                    Delete {{ selectedDeliveryCharge?.title }}? This delivery
                    charge will no longer appear in the list.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="deleteOpen = false">
                    Cancel
                </Button>
                <Button
                    variant="destructive"
                    :disabled="deleting"
                    @click="deleteDeliveryCharge"
                >
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
