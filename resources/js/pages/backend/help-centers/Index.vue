<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import {
    CircleHelp,
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

type HelpCenter = {
    id: number;
    title: string;
    items: string;
    item_count: number;
    sort_order: number;
    status: number;
    created_at: string | null;
};

const { helpCenters } = defineProps<{
    helpCenters: HelpCenter[];
}>();

const search = ref('');
const formOpen = ref(false);
const deleteOpen = ref(false);
const selectedHelpCenter = ref<HelpCenter | null>(null);
const deleting = ref(false);

const filteredHelpCenters = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return helpCenters;
    }

    return helpCenters.filter((helpCenter) =>
        [helpCenter.title, helpCenter.items]
            .filter(Boolean)
            .some((value) => value.toLowerCase().includes(query)),
    );
});

const openCreate = () => {
    selectedHelpCenter.value = null;
    formOpen.value = true;
};

const openEdit = (helpCenter: HelpCenter) => {
    selectedHelpCenter.value = helpCenter;
    formOpen.value = true;
};

const openDelete = (helpCenter: HelpCenter) => {
    selectedHelpCenter.value = helpCenter;
    deleteOpen.value = true;
};

const deleteHelpCenter = () => {
    if (!selectedHelpCenter.value) {
        return;
    }

    deleting.value = true;
    router.delete(`/help-centers/${selectedHelpCenter.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteOpen.value = false;
            selectedHelpCenter.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
};

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Help Center', href: '/help-centers' }],
    },
});
</script>

<template>
    <Head title="Help Center" />

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
                    placeholder="Search help center"
                    aria-label="Search help center"
                />
            </div>
            <Button class="shrink-0" @click="openCreate">
                <Plus class="size-4" />
                Add help item
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
                            <th class="px-4 py-3 font-medium">Questions</th>
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
                            v-for="(helpCenter, index) in filteredHelpCenters"
                            :key="helpCenter.id"
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
                                        <CircleHelp class="size-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate font-medium">
                                            {{ helpCenter.title }}
                                        </div>
                                        <div
                                            class="truncate text-muted-foreground"
                                        >
                                            {{ helpCenter.items }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 tabular-nums">
                                {{ helpCenter.item_count }}
                            </td>
                            <td class="px-4 py-3 tabular-nums">
                                {{ helpCenter.sort_order }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        helpCenter.status
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    {{
                                        helpCenter.status
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{
                                    formatDate(helpCenter.created_at) ||
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
                                            title="Help center actions"
                                        >
                                            <MoreVertical class="size-4" />
                                            <span class="sr-only"
                                                >Help center actions</span
                                            >
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            @click="openEdit(helpCenter)"
                                        >
                                            <Pencil class="size-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            variant="destructive"
                                            @click="openDelete(helpCenter)"
                                        >
                                            <Trash2 class="size-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="filteredHelpCenters.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <CircleHelp class="mx-auto mb-3 size-8" />
                                No help center items found
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
            style="width: min(640px, calc(100vw - 2rem)); max-width: 640px"
        >
            <DialogHeader class="gap-1.5 pr-6">
                <DialogTitle>
                    {{
                        selectedHelpCenter
                            ? 'Edit help center item'
                            : 'Add help center item'
                    }}
                </DialogTitle>
            </DialogHeader>

            <Form
                :key="selectedHelpCenter?.id ?? 'create'"
                method="post"
                :action="
                    selectedHelpCenter
                        ? `/help-centers/${selectedHelpCenter.id}`
                        : '/help-centers'
                "
                class="grid min-w-0 gap-3 [&_input]:focus-visible:ring-1 [&_input]:focus-visible:ring-ring/20"
                :reset-on-success="!selectedHelpCenter"
                v-slot="{ errors, processing }"
                @success="formOpen = false"
            >
                <div class="grid gap-2">
                    <Label for="help_center_title">Title</Label>
                    <Input
                        id="help_center_title"
                        name="title"
                        :default-value="selectedHelpCenter?.title"
                        placeholder="e.g. Order Support"
                        required
                    />
                    <InputError :message="errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="help_center_items">Support points</Label>
                    <textarea
                        id="help_center_items"
                        name="items"
                        :default-value="selectedHelpCenter?.items"
                        placeholder="Write each support point on a new line"
                        class="flex min-h-36 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-colors outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/20 disabled:cursor-not-allowed disabled:opacity-50"
                        required
                    />
                    <InputError :message="errors.items" />
                </div>

                <div class="grid gap-2">
                    <Label for="help_center_sort_order">Sort order</Label>
                    <Input
                        id="help_center_sort_order"
                        type="number"
                        name="sort_order"
                        min="0"
                        step="1"
                        :default-value="selectedHelpCenter?.sort_order ?? 0"
                    />
                    <InputError :message="errors.sort_order" />
                </div>
                <input
                    type="hidden"
                    name="status"
                    :value="selectedHelpCenter?.status ?? 1"
                />

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
                        {{ selectedHelpCenter ? 'Update' : 'Submit' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="deleteOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete help center item</DialogTitle>
                <DialogDescription>
                    Delete {{ selectedHelpCenter?.title }}? This item will no
                    longer appear in the help center list.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="deleteOpen = false">
                    Cancel
                </Button>
                <Button
                    variant="destructive"
                    :disabled="deleting"
                    @click="deleteHelpCenter"
                >
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
