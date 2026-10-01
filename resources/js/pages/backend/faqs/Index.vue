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

type Faq = {
    id: number;
    title: string;
    items: string;
    item_count: number;
    sort_order: number;
    status: number;
    created_at: string | null;
};

const { faqs } = defineProps<{
    faqs: Faq[];
}>();

const search = ref('');
const formOpen = ref(false);
const deleteOpen = ref(false);
const selectedFaq = ref<Faq | null>(null);
const deleting = ref(false);

const filteredFaqs = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return faqs;
    }

    return faqs.filter((faq) =>
        [faq.title, faq.items]
            .filter(Boolean)
            .some((value) => value.toLowerCase().includes(query)),
    );
});

const openCreate = () => {
    selectedFaq.value = null;
    formOpen.value = true;
};

const openEdit = (faq: Faq) => {
    selectedFaq.value = faq;
    formOpen.value = true;
};

const openDelete = (faq: Faq) => {
    selectedFaq.value = faq;
    deleteOpen.value = true;
};

const deleteFaq = () => {
    if (!selectedFaq.value) {
        return;
    }

    deleting.value = true;
    router.delete(`/faqs/${selectedFaq.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteOpen.value = false;
            selectedFaq.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
};

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'FAQ', href: '/faqs' }],
    },
});
</script>

<template>
    <Head title="FAQ" />

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
                    placeholder="Search FAQ"
                    aria-label="Search FAQ"
                />
            </div>
            <Button class="shrink-0" @click="openCreate">
                <Plus class="size-4" />
                Add FAQ item
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
                            v-for="(faq, index) in filteredFaqs"
                            :key="faq.id"
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
                                            {{ faq.title }}
                                        </div>
                                        <div
                                            class="truncate text-muted-foreground"
                                        >
                                            {{ faq.items }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 tabular-nums">
                                {{ faq.item_count }}
                            </td>
                            <td class="px-4 py-3 tabular-nums">
                                {{ faq.sort_order }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        faq.status
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    {{ faq.status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{
                                    formatDate(faq.created_at) ||
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
                                            title="FAQ actions"
                                        >
                                            <MoreVertical class="size-4" />
                                            <span class="sr-only"
                                                >FAQ actions</span
                                            >
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            @click="openEdit(faq)"
                                        >
                                            <Pencil class="size-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            variant="destructive"
                                            @click="openDelete(faq)"
                                        >
                                            <Trash2 class="size-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="filteredFaqs.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <FileText class="mx-auto mb-3 size-8" />
                                No FAQ items found
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
                    {{ selectedFaq ? 'Edit FAQ item' : 'Add FAQ item' }}
                </DialogTitle>
                <DialogDescription>
                    Add one answer per line. Active items appear on the public
                    FAQ page.
                </DialogDescription>
            </DialogHeader>

            <Form
                :key="selectedFaq?.id ?? 'create'"
                method="post"
                :action="selectedFaq ? `/faqs/${selectedFaq.id}` : '/faqs'"
                class="grid min-w-0 gap-5 [&_input]:focus-visible:ring-1 [&_input]:focus-visible:ring-ring/20"
                :reset-on-success="!selectedFaq"
                v-slot="{ errors, processing }"
                @success="formOpen = false"
            >
                <div class="grid gap-2">
                    <Label for="faq_title">Title</Label>
                    <Input
                        id="faq_title"
                        name="title"
                        :default-value="selectedFaq?.title"
                        placeholder="e.g. Ordering Questions"
                        required
                    />
                    <div class="min-h-5">
                        <InputError :message="errors.title" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="faq_items">Answers</Label>
                    <textarea
                        id="faq_items"
                        name="items"
                        :default-value="selectedFaq?.items"
                        placeholder="Write each answer on a new line"
                        class="flex min-h-36 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-colors outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/20 disabled:cursor-not-allowed disabled:opacity-50"
                        required
                    />
                    <div class="min-h-5">
                        <InputError :message="errors.items" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="faq_sort_order">Sort order</Label>
                        <Input
                            id="faq_sort_order"
                            type="number"
                            name="sort_order"
                            min="0"
                            step="1"
                            :default-value="selectedFaq?.sort_order ?? 0"
                        />
                        <div class="min-h-5">
                            <InputError :message="errors.sort_order" />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="faq_status">Status</Label>
                        <select
                            id="faq_status"
                            name="status"
                            :value="selectedFaq?.status ?? 1"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/20"
                        >
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div class="min-h-5">
                            <InputError :message="errors.status" />
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
                        {{ selectedFaq ? 'Update' : 'Submit' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="deleteOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete FAQ item</DialogTitle>
                <DialogDescription>
                    Delete {{ selectedFaq?.title }}? This item will no longer
                    appear in the FAQ list.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="deleteOpen = false">
                    Cancel
                </Button>
                <Button
                    variant="destructive"
                    :disabled="deleting"
                    @click="deleteFaq"
                >
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
