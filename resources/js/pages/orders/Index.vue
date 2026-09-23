<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Eye,
    MoreVertical,
    Pencil,
    Plus,
    Search,
    Trash2,
    UserRound,
} from '@lucide/vue';
import { computed, ref } from 'vue';
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

type Detail = {
    id: number;
    product_name: string;
    product_sku: string | null;
    order_qty: number;
    sale_price: string;
    discount_amount: string;
    total_amount: string;
};
type Order = {
    id: number;
    order_number: string;
    customer_name: string | null;
    customer_phone: string | null;
    customer_photo_url: string | null;
    total_product: number;
    subtotal: string;
    discount_amount: string;
    delivery_charge: string;
    total_amount: string;
    paid_amount: string;
    due_amount: string;
    payment_status: number;
    order_status: number;
    order_date: string | null;
    delivery_date: string | null;
    created_by_name: string | null;
    details: Detail[];
};
const { orders } = defineProps<{ orders: Order[] }>();
const search = ref('');
const selected = ref<Order | null>(null);
const detailsOpen = ref(false);
const deleteOpen = ref(false);
const deleting = ref(false);
const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();

    return q
        ? orders.filter((o) =>
              [
                  o.order_number,
                  o.customer_name,
                  o.customer_phone,
                  statusLabel(o.order_status),
              ]
                  .filter(Boolean)
                  .some((v) => String(v).toLowerCase().includes(q)),
          )
        : orders;
});
const money = (value: number | string) =>
    Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
const formatDate = (value: string | null) =>
    value
        ? new Intl.DateTimeFormat(undefined, {
              year: 'numeric',
              month: 'short',
              day: 'numeric',
          }).format(new Date(`${value}T00:00:00`))
        : '';
const statusLabel = (status: number) =>
    ['Pending', 'Processing', 'Delivered', 'Cancelled'][status] ?? 'Unknown';
const paymentLabel = (status: number) =>
    ['Unpaid', 'Paid', 'Partial'][status] ?? 'Unknown';
const showDetails = (order: Order) => {
    selected.value = order;
    detailsOpen.value = true;
};
const showDelete = (order: Order) => {
    selected.value = order;
    deleteOpen.value = true;
};
const deleteOrder = () => {
    if (!selected.value) {
        return;
    }

    deleting.value = true;
    router.delete(`/orders/${selected.value.id}`, {
        onFinish: () => {
            deleting.value = false;
        },
        onSuccess: () => {
            deleteOpen.value = false;
            selected.value = null;
        },
    });
};

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Orders', href: '/orders' }],
    },
});
</script>

<template>
    <Head title="Orders" />
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="relative w-full sm:max-w-sm">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    class="pl-9"
                    placeholder="Search orders"
                    aria-label="Search orders"
                />
            </div>
            <Button class="shrink-0" @click="router.visit('/orders/create')">
                <Plus class="size-4" />
                Add order
            </Button>
        </div>
        <div class="overflow-hidden rounded-md border">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Order</th>
                            <th class="px-4 py-3 font-medium">Customer</th>
                            <th class="px-4 py-3 text-center font-medium">
                                Items
                            </th>
                            <th class="px-4 py-3 text-right font-medium">
                                Total
                            </th>
                            <th class="px-4 py-3 font-medium">Payment</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Order date</th>
                            <th class="w-14 px-4 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="order in filtered"
                            :key="order.id"
                            class="hover:bg-muted/20"
                        >
                            <td class="px-4 py-3 font-medium">
                                {{ order.order_number }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <img
                                        v-if="order.customer_photo_url"
                                        :src="order.customer_photo_url"
                                        :alt="order.customer_name ?? ''"
                                        class="size-8 rounded-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex size-8 items-center justify-center rounded-full bg-muted"
                                    >
                                        <UserRound class="size-4" />
                                    </div>
                                    <div>
                                        <div class="font-medium">
                                            {{
                                                order.customer_name || 'Unknown'
                                            }}
                                        </div>
                                        <div
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                order.customer_phone ||
                                                'No phone'
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                {{ order.total_product }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium">
                                {{ money(order.total_amount) }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-sm px-2 py-1 text-xs font-medium"
                                    :class="
                                        order.payment_status === 1
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : order.payment_status === 2
                                              ? 'bg-amber-100 text-amber-700'
                                              : 'bg-red-100 text-red-700'
                                    "
                                    >{{
                                        paymentLabel(order.payment_status)
                                    }}</span
                                >
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-sm bg-muted px-2 py-1 text-xs font-medium"
                                    >{{ statusLabel(order.order_status) }}</span
                                >
                            </td>
                            <td class="px-4 py-3">
                                {{ formatDate(order.order_date) }}
                            </td>
                            <td class="px-4 py-3">
                                <DropdownMenu
                                    ><DropdownMenuTrigger as-child
                                        ><Button
                                            variant="ghost"
                                            size="icon"
                                            title="Order actions"
                                            ><MoreVertical
                                                class="size-4" /></Button></DropdownMenuTrigger
                                    ><DropdownMenuContent align="end"
                                        ><DropdownMenuItem
                                            @click="showDetails(order)"
                                            ><Eye
                                                class="size-4"
                                            />View</DropdownMenuItem
                                        ><DropdownMenuItem
                                            @click="
                                                router.visit(
                                                    `/orders/${order.id}/edit`,
                                                )
                                            "
                                            ><Pencil
                                                class="size-4"
                                            />Edit</DropdownMenuItem
                                        ><DropdownMenuItem
                                            variant="destructive"
                                            @click="showDelete(order)"
                                            ><Trash2
                                                class="size-4"
                                            />Delete</DropdownMenuItem
                                        ></DropdownMenuContent
                                    ></DropdownMenu
                                >
                            </td>
                        </tr>
                        <tr v-if="!filtered.length">
                            <td
                                colspan="8"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                No orders found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <Dialog v-model:open="detailsOpen"
        ><DialogContent class="sm:max-w-3xl"
            ><DialogHeader
                ><DialogTitle>{{ selected?.order_number }}</DialogTitle
                ><DialogDescription
                    >Order details for
                    {{ selected?.customer_name }}.</DialogDescription
                ></DialogHeader
            >
            <div v-if="selected" class="space-y-4">
                <div
                    class="grid gap-3 rounded-md border bg-muted/30 p-3 text-sm sm:grid-cols-3"
                >
                    <div>
                        <div class="text-xs text-muted-foreground">
                            Order date
                        </div>
                        <div class="font-medium">
                            {{ formatDate(selected.order_date) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-muted-foreground">
                            Delivery date
                        </div>
                        <div class="font-medium">
                            {{
                                formatDate(selected.delivery_date) || 'Not set'
                            }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-muted-foreground">Total</div>
                        <div class="font-medium">
                            {{ money(selected.total_amount) }}
                        </div>
                    </div>
                </div>
                <div class="max-h-80 overflow-auto rounded-md border">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 border-b bg-muted text-left">
                            <tr>
                                <th class="px-3 py-2 font-medium">Product</th>
                                <th class="px-3 py-2 text-right font-medium">
                                    Qty
                                </th>
                                <th class="px-3 py-2 text-right font-medium">
                                    Price
                                </th>
                                <th class="px-3 py-2 text-right font-medium">
                                    Discount
                                </th>
                                <th class="px-3 py-2 text-right font-medium">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="detail in selected.details"
                                :key="detail.id"
                            >
                                <td class="px-3 py-2">
                                    <div class="font-medium">
                                        {{ detail.product_name }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ detail.product_sku }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ detail.order_qty }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ money(detail.sale_price) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ money(detail.discount_amount) }}
                                </td>
                                <td class="px-3 py-2 text-right font-medium">
                                    {{ money(detail.total_amount) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div></DialogContent
        ></Dialog
    >
    <Dialog v-model:open="deleteOpen"
        ><DialogContent
            ><DialogHeader
                ><DialogTitle>Delete order</DialogTitle
                ><DialogDescription
                    >This will delete {{ selected?.order_number }} and return
                    its quantities to stock.</DialogDescription
                ></DialogHeader
            ><DialogFooter
                ><Button variant="outline" @click="deleteOpen = false"
                    >Cancel</Button
                ><Button
                    variant="destructive"
                    :disabled="deleting"
                    @click="deleteOrder"
                    >{{ deleting ? 'Deleting...' : 'Delete' }}</Button
                ></DialogFooter
            ></DialogContent
        ></Dialog
    >
</template>
