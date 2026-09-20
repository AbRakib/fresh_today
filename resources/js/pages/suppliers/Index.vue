<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { Camera, Pencil, Plus, Search, Trash2, Truck } from '@lucide/vue';
import { computed, onBeforeUnmount, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatDate } from '@/lib/utils';

type Supplier = {
    id: number;
    name: string;
    photo_url: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
    note: string | null;
    opening_balance_amount: string;
    opening_balance_date: string | null;
    status: number;
};

const { suppliers } = defineProps<{ suppliers: Supplier[] }>();

const search = ref('');
const formOpen = ref(false);
const deleteOpen = ref(false);
const selectedSupplier = ref<Supplier | null>(null);
const deleting = ref(false);
const photoPreviewUrl = ref<string | null>(null);

const displayedPhotoUrl = computed(
    () => photoPreviewUrl.value ?? selectedSupplier.value?.photo_url ?? null,
);

const filteredSuppliers = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return suppliers;
    }

    return suppliers.filter((supplier) =>
        [supplier.name, supplier.email, supplier.phone, supplier.address]
            .filter(Boolean)
            .some((value) => value!.toLowerCase().includes(query)),
    );
});

const initials = (name: string) =>
    name
        .split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();

const resetPhotoPreview = () => {
    if (photoPreviewUrl.value) {
        URL.revokeObjectURL(photoPreviewUrl.value);
    }

    photoPreviewUrl.value = null;
};

const handlePhotoChange = (event: Event) => {
    resetPhotoPreview();

    const file = (event.target as HTMLInputElement).files?.[0];

    if (file) {
        photoPreviewUrl.value = URL.createObjectURL(file);
    }
};

const openCreate = () => {
    resetPhotoPreview();
    selectedSupplier.value = null;
    formOpen.value = true;
};

const openEdit = (supplier: Supplier) => {
    resetPhotoPreview();
    selectedSupplier.value = supplier;
    formOpen.value = true;
};

const openDelete = (supplier: Supplier) => {
    selectedSupplier.value = supplier;
    deleteOpen.value = true;
};

const deleteSupplier = () => {
    if (!selectedSupplier.value) {
        return;
    }

    deleting.value = true;
    router.delete(`/suppliers/${selectedSupplier.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteOpen.value = false;
            selectedSupplier.value = null;
        },
        onFinish: () => (deleting.value = false),
    });
};

onBeforeUnmount(resetPhotoPreview);

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Suppliers', href: '/suppliers' }],
    },
});
</script>

<template>
    <Head title="Suppliers" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <Heading
                title="Suppliers"
                description="Manage supplier contacts and purchasing details"
            />
            <Button class="shrink-0" @click="openCreate">
                <Plus class="size-4" />
                Add supplier
            </Button>
        </div>

        <div class="relative max-w-sm">
            <Search
                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
            />
            <Input
                v-model="search"
                class="pl-9"
                placeholder="Search suppliers"
                aria-label="Search suppliers"
            />
        </div>

        <div class="overflow-hidden rounded-md border">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="w-16 px-4 py-3 font-medium">SL</th>
                            <th class="px-4 py-3 font-medium">Supplier</th>
                            <th class="px-4 py-3 font-medium">Phone</th>
                            <th class="px-4 py-3 font-medium">
                                Opening balance
                            </th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="w-24 px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="(supplier, index) in filteredSuppliers"
                            :key="supplier.id"
                            class="hover:bg-muted/30"
                        >
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ index + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <Avatar class="size-9">
                                        <AvatarImage
                                            v-if="supplier.photo_url"
                                            :src="supplier.photo_url"
                                            :alt="supplier.name"
                                        />
                                        <AvatarFallback>{{
                                            initials(supplier.name)
                                        }}</AvatarFallback>
                                    </Avatar>
                                    <div class="min-w-0">
                                        <div class="font-medium">
                                            {{ supplier.name }}
                                        </div>
                                        <div
                                            class="truncate text-muted-foreground"
                                        >
                                            {{ supplier.email || 'No email' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ supplier.phone || 'Not provided' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">
                                    {{ supplier.opening_balance_amount }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{
                                        formatDate(
                                            supplier.opening_balance_date,
                                        ) || 'No date'
                                    }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        supplier.status
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    {{
                                        supplier.status ? 'Active' : 'Inactive'
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        title="Edit supplier"
                                        @click="openEdit(supplier)"
                                    >
                                        <Pencil class="size-4" /><span
                                            class="sr-only"
                                            >Edit supplier</span
                                        >
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="text-destructive hover:text-destructive"
                                        title="Delete supplier"
                                        @click="openDelete(supplier)"
                                    >
                                        <Trash2 class="size-4" /><span
                                            class="sr-only"
                                            >Delete supplier</span
                                        >
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredSuppliers.length === 0">
                            <td
                                colspan="6"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <Truck class="mx-auto mb-3 size-8" />
                                No suppliers found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <Dialog v-model:open="formOpen">
        <DialogContent
            class="max-h-[calc(100vh-2rem)] gap-0 overflow-hidden p-0 sm:max-w-2xl"
            style="width: min(680px, calc(100vw - 2rem))"
        >
            <DialogHeader
                class="border-b bg-muted/30 px-5 py-4 text-left sm:px-6"
            >
                <div class="flex items-center gap-3 pr-8">
                    <Avatar class="size-12 border bg-background">
                        <AvatarImage
                            v-if="displayedPhotoUrl"
                            :src="displayedPhotoUrl"
                            :alt="selectedSupplier?.name ?? 'Supplier photo'"
                        />
                        <AvatarFallback>{{
                            selectedSupplier
                                ? initials(selectedSupplier.name)
                                : 'NS'
                        }}</AvatarFallback>
                    </Avatar>
                    <div class="min-w-0">
                        <DialogTitle class="text-lg">{{
                            selectedSupplier ? 'Edit supplier' : 'Add supplier'
                        }}</DialogTitle>
                        <DialogDescription class="mt-1 truncate text-sm">
                            {{
                                selectedSupplier
                                    ? selectedSupplier.email ||
                                      selectedSupplier.phone ||
                                      'Update supplier details.'
                                    : 'Create a new supplier profile.'
                            }}
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <Form
                :key="selectedSupplier?.id ?? 'create'"
                method="post"
                :action="
                    selectedSupplier
                        ? `/suppliers/${selectedSupplier.id}`
                        : '/suppliers'
                "
                class="flex min-h-0 flex-col [&_input]:focus-visible:ring-1 [&_input]:focus-visible:ring-ring/30 [&_textarea]:focus-visible:ring-1 [&_textarea]:focus-visible:ring-ring/30"
                :reset-on-success="!selectedSupplier"
                v-slot="{ errors, processing }"
                @success="formOpen = false"
            >
                <div class="grid gap-5 overflow-y-auto px-5 py-5 sm:px-6">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="supplier_name">Name</Label>
                            <Input
                                id="supplier_name"
                                name="name"
                                :default-value="selectedSupplier?.name"
                                required
                            />
                            <InputError :message="errors.name" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="supplier_email">Email</Label>
                            <Input
                                id="supplier_email"
                                type="email"
                                name="email"
                                :default-value="selectedSupplier?.email ?? ''"
                            />
                            <InputError :message="errors.email" />
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="supplier_phone">Phone</Label>
                            <Input
                                id="supplier_phone"
                                name="phone"
                                :default-value="selectedSupplier?.phone ?? ''"
                            />
                            <InputError :message="errors.phone" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="supplier_photo">Photo</Label>
                            <div class="relative">
                                <Camera
                                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <Input
                                    id="supplier_photo"
                                    type="file"
                                    name="photo"
                                    accept="image/*"
                                    class="pl-9"
                                    @change="handlePhotoChange"
                                />
                            </div>
                            <InputError :message="errors.photo" />
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="supplier_opening_balance_amount"
                                >Opening balance amount</Label
                            >
                            <Input
                                id="supplier_opening_balance_amount"
                                type="number"
                                name="opening_balance_amount"
                                min="0"
                                step="0.01"
                                :default-value="
                                    selectedSupplier?.opening_balance_amount ??
                                    '0.00'
                                "
                                required
                            />
                            <InputError
                                :message="errors.opening_balance_amount"
                            />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="supplier_opening_balance_date"
                                >Opening balance date</Label
                            >
                            <Input
                                id="supplier_opening_balance_date"
                                type="date"
                                name="opening_balance_date"
                                :default-value="
                                    selectedSupplier?.opening_balance_date ?? ''
                                "
                            />
                            <InputError
                                :message="errors.opening_balance_date"
                            />
                        </div>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="supplier_address">Address</Label>
                        <textarea
                            id="supplier_address"
                            name="address"
                            rows="3"
                            :value="selectedSupplier?.address ?? ''"
                            class="w-full resize-none rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring"
                        />
                        <InputError :message="errors.address" />
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="supplier_note">Note</Label>
                        <textarea
                            id="supplier_note"
                            name="note"
                            rows="3"
                            :value="selectedSupplier?.note ?? ''"
                            class="w-full resize-none rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring"
                        />
                        <InputError :message="errors.note" />
                    </div>
                </div>

                <DialogFooter class="border-t bg-background px-5 py-4 sm:px-6">
                    <Button
                        type="button"
                        variant="outline"
                        @click="formOpen = false"
                        >Cancel</Button
                    >
                    <Button type="submit" :disabled="processing">
                        {{
                            processing
                                ? 'Saving...'
                                : selectedSupplier
                                  ? 'Save changes'
                                  : 'Create supplier'
                        }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="deleteOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete supplier</DialogTitle>
                <DialogDescription
                    >Delete {{ selectedSupplier?.name }}? This supplier will no
                    longer appear in the supplier list.</DialogDescription
                >
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="deleteOpen = false"
                    >Cancel</Button
                >
                <Button
                    variant="destructive"
                    :disabled="deleting"
                    @click="deleteSupplier"
                    >Delete</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
