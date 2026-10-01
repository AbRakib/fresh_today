<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import {
    FileText,
    MoreVertical,
    Pencil,
    Plus,
    Search,
    Trash2,
} from '@lucide/vue';
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

type ReturnPolicy = {
    id: number;
    title: string;
    items: string;
    item_count: number;
    sort_order: number;
    status: number;
    created_at: string | null;
};

const { returnPolicies } = defineProps<{
    returnPolicies: ReturnPolicy[];
}>();

const search = ref('');
const formOpen = ref(false);
const deleteOpen = ref(false);
const selectedReturnPolicy = ref<ReturnPolicy | null>(null);
const deleting = ref(false);

const filteredReturnPolicies = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return returnPolicies;
    }

    return returnPolicies.filter((returnPolicy) =>
        [returnPolicy.title, returnPolicy.items]
            .filter(Boolean)
            .some((value) => value.toLowerCase().includes(query)),
    );
});

const openCreate = () => {
    selectedReturnPolicy.value = null;
    formOpen.value = true;
};

const openEdit = (returnPolicy: ReturnPolicy) => {
    selectedReturnPolicy.value = returnPolicy;
    formOpen.value = true;
};

const openDelete = (returnPolicy: ReturnPolicy) => {
    selectedReturnPolicy.value = returnPolicy;
    deleteOpen.value = true;
};

const deleteReturnPolicy = () => {
    if (!selectedReturnPolicy.value) {
        return;
    }

    deleting.value = true;
    router.delete(`/return-policies/${selectedReturnPolicy.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteOpen.value = false;
            selectedReturnPolicy.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
};

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Return Policy', href: '/return-policies' }],
    },
});
</script>

<template>
    <Head title="Return Policy" />

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
                    placeholder="Search return policy"
                    aria-label="Search return policy"
                />
            </div>
            <Button class="shrink-0" @click="openCreate">
                <Plus class="size-4" />
                Add return item
            </Button>
        </div>

        <div class="overflow-hidden rounded-md border">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] table-fixed text-sm">
                    <colgroup>
                        <col class="w-[6%]" />
                        <col class="w-[36%]" />
                        <col class="w-[14%]" />
                        <col class="w-[12%]" />
                        <col class="w-[12%]" />
                        <col class="w-[12%]" />
                        <col class="w-[8%]" />
                    </colgroup>
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">SL</th>
                            <th class="px-4 py-3 font-medium">Title</th>
                            <th class="px-4 py-3 font-medium">Points</th>
                            <th class="px-4 py-3 font-medium">Sort</th>
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
                                returnPolicy, index
                            ) in filteredReturnPolicies"
                            :key="returnPolicy.id"
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
                                        <FileText class="size-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate font-medium">
                                            {{ returnPolicy.title }}
                                        </div>
                                        <div
                                            class="truncate text-muted-foreground"
                                        >
                                            {{ returnPolicy.items }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 tabular-nums">
                                {{ returnPolicy.item_count }}
                            </td>
                            <td class="px-4 py-3 tabular-nums">
                                {{ returnPolicy.sort_order }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        returnPolicy.status
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    {{
                                        returnPolicy.status
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{
                                    formatDate(returnPolicy.created_at) ||
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
                                            title="Return policy actions"
                                        >
                                            <MoreVertical class="size-4" />
                                            <span class="sr-only"
                                                >Return policy actions</span
                                            >
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            @click="openEdit(returnPolicy)"
                                        >
                                            <Pencil class="size-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            variant="destructive"
                                            @click="openDelete(returnPolicy)"
                                        >
                                            <Trash2 class="size-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="filteredReturnPolicies.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <FileText class="mx-auto mb-3 size-8" />
                                No return policy items found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <Dialog v-model:open="formOpen">
        <DialogContent
            class="max-h-[calc(100vh-2rem)] gap-4 overflow-x-hidden overflow-y-auto p-6"
            style="width: min(640px, calc(100vw - 2rem)); max-width: 640px"
        >
            <DialogHeader class="gap-1.5 pr-6">
                <DialogTitle>
                    {{
                        selectedReturnPolicy
                            ? 'Edit return policy item'
                            : 'Add return policy item'
                    }}
                </DialogTitle>
            </DialogHeader>

            <Form
                :key="selectedReturnPolicy?.id ?? 'create'"
                method="post"
                :action="
                    selectedReturnPolicy
                        ? `/return-policies/${selectedReturnPolicy.id}`
                        : '/return-policies'
                "
                class="grid min-w-0 gap-3 [&_input]:focus-visible:ring-1 [&_input]:focus-visible:ring-ring/20"
                :reset-on-success="!selectedReturnPolicy"
                v-slot="{ errors, processing }"
                @success="formOpen = false"
            >
                <div class="grid gap-2">
                    <Label for="return_policy_title">Title</Label>
                    <Input
                        id="return_policy_title"
                        name="title"
                        :default-value="selectedReturnPolicy?.title"
                        placeholder="e.g. Return Eligibility"
                        required
                    />
                    <InputError :message="errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="return_policy_items">Return points</Label>
                    <textarea
                        id="return_policy_items"
                        name="items"
                        :default-value="selectedReturnPolicy?.items"
                        placeholder="Write each return point on a new line"
                        class="flex min-h-36 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-colors outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/20 disabled:cursor-not-allowed disabled:opacity-50"
                        required
                    />
                    <InputError :message="errors.items" />
                </div>

                <div class="grid gap-2">
                    <Label for="return_policy_sort_order">Sort order</Label>
                    <Input
                        id="return_policy_sort_order"
                        type="number"
                        name="sort_order"
                        min="0"
                        step="1"
                        :default-value="selectedReturnPolicy?.sort_order ?? 0"
                    />
                    <InputError :message="errors.sort_order" />
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
                        {{ selectedReturnPolicy ? 'Update' : 'Submit' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="deleteOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete return policy item</DialogTitle>
                <DialogDescription>
                    Delete {{ selectedReturnPolicy?.title }}? This item will no
                    longer appear in the return policy list.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="deleteOpen = false">
                    Cancel
                </Button>
                <Button
                    variant="destructive"
                    :disabled="deleting"
                    @click="deleteReturnPolicy"
                >
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
