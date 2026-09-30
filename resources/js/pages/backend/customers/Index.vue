<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import {
    KeyRound,
    MoreVertical,
    Pencil,
    Plus,
    Search,
    Trash2,
    UserRound,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import ImageCropInput from '@/components/ImageCropInput.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Customer = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    profile_image_url: string | null;
    address: string | null;
    gender: string | null;
    date_of_birth: string | null;
    status: number;
};

const { customers } = defineProps<{ customers: Customer[] }>();

const search = ref('');
const formOpen = ref(false);
const passwordOpen = ref(false);
const deleteOpen = ref(false);
const selectedCustomer = ref<Customer | null>(null);
const customerPhotoPreview = ref<string | null>(null);
const deleting = ref(false);

const filteredCustomers = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return customers;
    }

    return customers.filter((customer) =>
        [customer.name, customer.email, customer.phone]
            .filter(Boolean)
            .some((value) => value!.toLowerCase().includes(query)),
    );
});

const displayedCustomerPhoto = computed(
    () =>
        customerPhotoPreview.value ??
        selectedCustomer.value?.profile_image_url ??
        null,
);

const initials = (name: string) =>
    name
        .split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();

const openCreate = () => {
    selectedCustomer.value = null;
    customerPhotoPreview.value = null;
    formOpen.value = true;
};

const openEdit = (customer: Customer) => {
    selectedCustomer.value = customer;
    customerPhotoPreview.value = null;
    formOpen.value = true;
};

const openChangePassword = (customer: Customer) => {
    selectedCustomer.value = customer;
    passwordOpen.value = true;
};

const handleCustomerPhotoCropped = (_file: File, previewUrl: string) => {
    customerPhotoPreview.value = previewUrl;
};

const openDelete = (customer: Customer) => {
    selectedCustomer.value = customer;
    deleteOpen.value = true;
};

const deleteCustomer = () => {
    if (!selectedCustomer.value) {
        return;
    }

    deleting.value = true;
    router.delete(`/customers/${selectedCustomer.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteOpen.value = false;
            selectedCustomer.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
};

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Customers', href: '/customers' }],
    },
});
</script>

<template>
    <Head title="Customers" />

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
                    placeholder="Search customers"
                    aria-label="Search customers"
                />
            </div>
            <Button class="shrink-0" @click="openCreate">
                <Plus class="size-4" />
                Add customer
            </Button>
        </div>

        <div class="overflow-hidden rounded-md border">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="w-16 px-4 py-3 font-medium">SL</th>
                            <th class="px-4 py-3 font-medium">Customer</th>
                            <th class="px-4 py-3 font-medium">Phone</th>
                            <th class="px-4 py-3 font-medium">Gender</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="w-24 px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="(customer, index) in filteredCustomers"
                            :key="customer.id"
                            class="hover:bg-muted/30"
                        >
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ index + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <Avatar class="size-9">
                                        <AvatarImage
                                            v-if="customer.profile_image_url"
                                            :src="customer.profile_image_url"
                                            :alt="customer.name"
                                        />
                                        <AvatarFallback>{{
                                            initials(customer.name)
                                        }}</AvatarFallback>
                                    </Avatar>
                                    <div class="min-w-0">
                                        <div class="font-medium">
                                            {{ customer.name }}
                                        </div>
                                        <div
                                            class="truncate text-muted-foreground"
                                        >
                                            {{ customer.email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ customer.phone || 'Not provided' }}
                            </td>
                            <td
                                class="px-4 py-3 text-muted-foreground capitalize"
                            >
                                {{ customer.gender || 'Not specified' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        customer.status
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    {{
                                        customer.status ? 'Active' : 'Inactive'
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="ml-auto flex"
                                            title="Customer actions"
                                        >
                                            <MoreVertical class="size-4" />
                                            <span class="sr-only"
                                                >Customer actions</span
                                            >
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            @click="openEdit(customer)"
                                        >
                                            <Pencil class="size-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="
                                                openChangePassword(customer)
                                            "
                                        >
                                            <KeyRound class="size-4" />
                                            Change password
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            variant="destructive"
                                            @click="openDelete(customer)"
                                        >
                                            <Trash2 class="size-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="filteredCustomers.length === 0">
                            <td
                                colspan="6"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <UserRound class="mx-auto mb-3 size-8" />
                                No customers found
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
                            v-if="displayedCustomerPhoto"
                            :src="displayedCustomerPhoto"
                            :alt="selectedCustomer?.name ?? 'Customer photo'"
                        />
                        <AvatarFallback class="text-sm font-medium">
                            {{
                                selectedCustomer
                                    ? initials(selectedCustomer.name)
                                    : 'NC'
                            }}
                        </AvatarFallback>
                    </Avatar>
                    <div class="min-w-0">
                        <DialogTitle class="text-lg">
                            {{
                                selectedCustomer
                                    ? 'Edit customer'
                                    : 'Add customer'
                            }}
                        </DialogTitle>
                        <DialogDescription class="mt-1 truncate text-sm">
                            {{
                                selectedCustomer
                                    ? selectedCustomer.email
                                    : 'Create a customer profile and account access.'
                            }}
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <Form
                :key="selectedCustomer?.id ?? 'create'"
                method="post"
                :action="
                    selectedCustomer
                        ? `/customers/${selectedCustomer.id}`
                        : '/customers'
                "
                class="flex min-h-0 flex-col [&_input]:focus-visible:ring-1 [&_input]:focus-visible:ring-ring/20 [&_select]:focus-visible:ring-1 [&_select]:focus-visible:ring-ring/20 [&_textarea]:focus-visible:ring-1 [&_textarea]:focus-visible:ring-ring/20"
                :reset-on-success="!selectedCustomer"
                v-slot="{ errors, processing }"
                @success="formOpen = false"
            >
                <div class="grid gap-5 overflow-y-auto px-5 py-5 sm:px-6">
                    <div class="grid gap-3">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="grid gap-1.5">
                                <Label for="customer_name">Name</Label>
                                <Input
                                    id="customer_name"
                                    name="name"
                                    placeholder="Enter customer name"
                                    :default-value="selectedCustomer?.name"
                                    required
                                />
                                <InputError :message="errors.name" />
                            </div>
                            <div class="grid gap-1.5">
                                <Label for="customer_email">Email</Label>
                                <Input
                                    id="customer_email"
                                    type="email"
                                    name="email"
                                    placeholder="Enter email address"
                                    :default-value="selectedCustomer?.email"
                                    required
                                />
                                <InputError :message="errors.email" />
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="customer_phone">Phone</Label>
                            <Input
                                id="customer_phone"
                                name="phone"
                                placeholder="Enter phone number"
                                :default-value="selectedCustomer?.phone ?? ''"
                                required
                            />
                            <InputError :message="errors.phone" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="customer_birth_date"
                                >Date of birth</Label
                            >
                            <Input
                                id="customer_birth_date"
                                type="date"
                                name="date_of_birth"
                                :default-value="
                                    selectedCustomer?.date_of_birth ?? ''
                                "
                            />
                            <InputError :message="errors.date_of_birth" />
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="customer_gender">Gender</Label>
                            <select
                                id="customer_gender"
                                name="gender"
                                :value="selectedCustomer?.gender ?? ''"
                                class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-1 focus-visible:ring-ring/20"
                            >
                                <option value="">Select gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <InputError :message="errors.gender" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="customer_address">Address</Label>
                            <textarea
                                id="customer_address"
                                name="address"
                                rows="1"
                                placeholder="Enter address"
                                :value="selectedCustomer?.address ?? ''"
                                class="h-9 w-full resize-none rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-1 focus-visible:ring-ring/20"
                            />
                            <InputError :message="errors.address" />
                        </div>
                    </div>

                    <div v-if="!selectedCustomer" class="grid gap-3">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="grid gap-1.5">
                                <Label for="customer_password">Password</Label>
                                <Input
                                    id="customer_password"
                                    type="password"
                                    name="password"
                                    placeholder="Enter password"
                                    :required="!selectedCustomer"
                                    autocomplete="new-password"
                                />
                                <InputError :message="errors.password" />
                            </div>
                            <div class="grid gap-1.5">
                                <Label for="customer_password_confirmation"
                                    >Confirm password</Label
                                >
                                <Input
                                    id="customer_password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    placeholder="Confirm password"
                                    :required="!selectedCustomer"
                                    autocomplete="new-password"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="grid justify-items-center gap-3">
                        <ImageCropInput
                            class="w-fit justify-items-center text-center"
                            id="customer_image"
                            name="profile_image"
                            label="Profile image"
                            :current-url="selectedCustomer?.profile_image_url"
                            choose-label="Upload"
                            :aspect-ratio="1"
                            :output-width="600"
                            :output-height="600"
                            preview-class="aspect-square w-28 rounded-full"
                            :show-header="false"
                            :show-status="false"
                            :error="errors.profile_image"
                            @cropped="handleCustomerPhotoCropped"
                        />
                    </div>
                </div>

                <DialogFooter class="border-t bg-background px-5 py-4 sm:px-6">
                    <Button
                        type="button"
                        variant="outline"
                        class="cursor-pointer"
                        @click="formOpen = false"
                        >Cancel</Button
                    >
                    <Button
                        type="submit"
                        class="cursor-pointer"
                        :disabled="processing"
                    >
                        {{
                            processing
                                ? 'Saving...'
                                : selectedCustomer
                                  ? 'Save changes'
                                  : 'Create customer'
                        }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="passwordOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Change password</DialogTitle>
                <DialogDescription>
                    Set a new password for {{ selectedCustomer?.name }}.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-if="selectedCustomer"
                method="patch"
                :action="`/customers/${selectedCustomer.id}/password`"
                reset-on-success
                class="grid gap-4"
                v-slot="{ errors, processing }"
                @success="passwordOpen = false"
            >
                <div class="grid gap-1.5">
                    <Label for="new_customer_password">New password</Label>
                    <PasswordInput
                        id="new_customer_password"
                        name="password"
                        autocomplete="new-password"
                        placeholder="Enter new password"
                        required
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="new_customer_password_confirmation">
                        Confirm password
                    </Label>
                    <PasswordInput
                        id="new_customer_password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        placeholder="Confirm new password"
                        required
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="passwordOpen = false"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Updating...' : 'Submit' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="deleteOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete customer</DialogTitle>
                <DialogDescription>
                    Delete {{ selectedCustomer?.name }}? This customer will no
                    longer appear in the customer list.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="deleteOpen = false"
                    >Cancel</Button
                >
                <Button
                    variant="destructive"
                    :disabled="deleting"
                    @click="deleteCustomer"
                    >Delete</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
