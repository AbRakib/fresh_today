<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { MoreVertical, Pencil, Plus, Ruler, Search, Trash2 } from '@lucide/vue';
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

type Unit = {
    id: number;
    name: string;
    default: number;
    status: number;
    created_at: string | null;
};

const { units } = defineProps<{ units: Unit[] }>();

const search = ref('');
const formOpen = ref(false);
const deleteOpen = ref(false);
const defaultConfirmOpen = ref(false);
const selectedUnit = ref<Unit | null>(null);
const deleting = ref(false);
const changingDefault = ref(false);

const filteredUnits = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return units;
    }

    return units.filter((unit) => unit.name.toLowerCase().includes(query));
});

const openCreate = () => {
    selectedUnit.value = null;
    formOpen.value = true;
};

const openEdit = (unit: Unit) => {
    selectedUnit.value = unit;
    formOpen.value = true;
};

const openDelete = (unit: Unit) => {
    selectedUnit.value = unit;
    deleteOpen.value = true;
};

const openDefaultConfirmation = (unit: Unit) => {
    selectedUnit.value = unit;
    defaultConfirmOpen.value = true;
};

const toggleDefault = () => {
    if (!selectedUnit.value) {
        return;
    }

    changingDefault.value = true;
    router.post(
        `/units/${selectedUnit.value.id}/toggle-default`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                defaultConfirmOpen.value = false;
                selectedUnit.value = null;
            },
            onFinish: () => {
                changingDefault.value = false;
            },
        },
    );
};

const deleteUnit = () => {
    if (!selectedUnit.value) {
        return;
    }

    deleting.value = true;
    router.delete(`/units/${selectedUnit.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteOpen.value = false;
            selectedUnit.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
};

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Units', href: '/units' }],
    },
});
</script>

<template>
    <Head title="Units" />

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
                    placeholder="Search units"
                    aria-label="Search units"
                />
            </div>
            <Button class="shrink-0" @click="openCreate">
                <Plus class="size-4" />
                Add unit
            </Button>
        </div>

        

        <div class="overflow-hidden rounded-md border">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="w-16 px-4 py-3 font-medium">SL</th>
                            <th class="px-4 py-3 font-medium">Unit</th>
                            <th class="px-4 py-3 font-medium">Default</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Created</th>
                            <th class="w-24 px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="(unit, index) in filteredUnits"
                            :key="unit.id"
                            class="hover:bg-muted/30"
                        >
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ index + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-9 items-center justify-center rounded-md bg-muted text-muted-foreground"
                                    >
                                        <Ruler class="size-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium">
                                            {{ unit.name }}
                                        </div>
                                        <div
                                            class="truncate text-muted-foreground"
                                        >
                                            Unit #{{ unit.id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <Button
                                    size="sm"
                                    :variant="
                                        unit.default ? 'outline' : 'default'
                                    "
                                    class="h-7 cursor-pointer px-2.5 text-xs"
                                    @click="openDefaultConfirmation(unit)"
                                >
                                    {{ unit.default ? 'Deactive' : 'Active' }}
                                </Button>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        unit.status
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    {{ unit.status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{
                                    formatDate(unit.created_at) ||
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
                                            title="Unit actions"
                                        >
                                            <MoreVertical class="size-4" />
                                            <span class="sr-only"
                                                >Unit actions</span
                                            >
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            @click="openEdit(unit)"
                                        >
                                            <Pencil class="size-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            variant="destructive"
                                            @click="openDelete(unit)"
                                        >
                                            <Trash2 class="size-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="filteredUnits.length === 0">
                            <td
                                colspan="6"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <Ruler class="mx-auto mb-3 size-8" />
                                No units found
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
                <DialogTitle>{{
                    selectedUnit ? 'Edit unit' : 'Add unit'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        selectedUnit
                            ? 'Update the unit information below.'
                            : 'Enter the new unit information below.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Form
                :key="selectedUnit?.id ?? 'create'"
                method="post"
                :action="selectedUnit ? `/units/${selectedUnit.id}` : '/units'"
                class="grid min-w-0 gap-5 [&_input]:focus-visible:ring-1 [&_input]:focus-visible:ring-ring/20"
                :reset-on-success="!selectedUnit"
                v-slot="{ errors, processing }"
                @success="formOpen = false"
            >
                <div class="grid gap-2">
                    <Label for="unit_name">Name</Label>
                    <Input
                        id="unit_name"
                        name="name"
                        :default-value="selectedUnit?.name"
                        placeholder="Enter unit name"
                        required
                    />
                    <InputError :message="errors.name" />
                </div>

                <DialogFooter class="border-t pt-4">
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
                        {{ selectedUnit ? 'Update' : 'Submit' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="defaultConfirmOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>
                    {{ selectedUnit?.default ? 'Deactivate' : 'Activate' }}
                    default unit
                </DialogTitle>
                <DialogDescription>
                    Are you sure you want to
                    {{ selectedUnit?.default ? 'deactivate' : 'activate' }}
                    {{ selectedUnit?.name }} as the default unit?
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
                <DialogTitle>Delete unit</DialogTitle>
                <DialogDescription>
                    Delete {{ selectedUnit?.name }}? This unit will no longer
                    appear in the unit list.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="deleteOpen = false"
                    >Cancel</Button
                >
                <Button
                    variant="destructive"
                    :disabled="deleting"
                    @click="deleteUnit"
                    >Delete</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
