<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import {
    ImageIcon,
    Layers3,
    MoreVertical,
    Pencil,
    Plus,
    Search,
    Trash2,
    Upload,
} from '@lucide/vue';
import { computed, onBeforeUnmount, ref } from 'vue';
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

type Category = {
    id: number;
    name: string;
};

type Subcategory = {
    id: number;
    category_id: number;
    category_name: string | null;
    name: string;
    icon_url: string | null;
    status: number;
    created_at: string | null;
};

const { subcategories, categories } = defineProps<{
    subcategories: Subcategory[];
    categories: Category[];
}>();

const search = ref('');
const formOpen = ref(false);
const deleteOpen = ref(false);
const selectedSubcategory = ref<Subcategory | null>(null);
const deleting = ref(false);
const iconPreviewUrl = ref<string | null>(null);
const iconFileName = ref('');

const displayedIconUrl = computed(
    () => iconPreviewUrl.value ?? selectedSubcategory.value?.icon_url ?? null,
);

const resetIconSelection = () => {
    if (iconPreviewUrl.value) {
        URL.revokeObjectURL(iconPreviewUrl.value);
    }

    iconPreviewUrl.value = null;
    iconFileName.value = '';
};

const handleIconChange = (event: Event) => {
    resetIconSelection();

    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    iconFileName.value = file.name;
    iconPreviewUrl.value = URL.createObjectURL(file);
};

const filteredSubcategories = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return subcategories;
    }

    return subcategories.filter((subcategory) =>
        [subcategory.name, subcategory.category_name]
            .filter(Boolean)
            .some((value) => value!.toLowerCase().includes(query)),
    );
});

const openCreate = () => {
    resetIconSelection();
    selectedSubcategory.value = null;
    formOpen.value = true;
};

const openEdit = (subcategory: Subcategory) => {
    resetIconSelection();
    selectedSubcategory.value = subcategory;
    formOpen.value = true;
};

const openDelete = (subcategory: Subcategory) => {
    selectedSubcategory.value = subcategory;
    deleteOpen.value = true;
};

const deleteSubcategory = () => {
    if (!selectedSubcategory.value) {
        return;
    }

    deleting.value = true;
    router.delete(`/subcategories/${selectedSubcategory.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteOpen.value = false;
            selectedSubcategory.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
};

onBeforeUnmount(resetIconSelection);

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Subcategories', href: '/subcategories' }],
    },
});
</script>

<template>
    <Head title="Subcategories" />

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
                    placeholder="Search subcategories"
                    aria-label="Search subcategories"
                />
            </div>
            <Button
                class="shrink-0"
                :disabled="categories.length === 0"
                @click="openCreate"
            >
                <Plus class="size-4" />
                Add subcategory
            </Button>
        </div>

        

        <div class="overflow-hidden rounded-md border">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="w-16 px-4 py-3 font-medium">SL</th>
                            <th class="px-4 py-3 font-medium">Subcategory</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Icon</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Created</th>
                            <th class="w-24 px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="(
                                subcategory, index
                            ) in filteredSubcategories"
                            :key="subcategory.id"
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
                                        <Layers3 class="size-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium">
                                            {{ subcategory.name }}
                                        </div>
                                        <div
                                            class="truncate text-muted-foreground"
                                        >
                                            Subcategory #{{ subcategory.id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{
                                    subcategory.category_name || 'Not assigned'
                                }}
                            </td>
                            <td class="px-4 py-3">
                                <img
                                    v-if="subcategory.icon_url"
                                    :src="subcategory.icon_url"
                                    :alt="`${subcategory.name} icon`"
                                    class="size-10 rounded-md border object-cover"
                                />
                                <div
                                    v-else
                                    class="flex size-10 items-center justify-center rounded-md border bg-muted text-muted-foreground"
                                >
                                    <Layers3 class="size-4" />
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        subcategory.status
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    {{
                                        subcategory.status
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{
                                    formatDate(subcategory.created_at) ||
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
                                            title="Subcategory actions"
                                        >
                                            <MoreVertical class="size-4" />
                                            <span class="sr-only"
                                                >Subcategory actions</span
                                            >
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            @click="openEdit(subcategory)"
                                        >
                                            <Pencil class="size-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            variant="destructive"
                                            @click="openDelete(subcategory)"
                                        >
                                            <Trash2 class="size-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="filteredSubcategories.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <Layers3 class="mx-auto mb-3 size-8" />
                                No subcategories found
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
                    selectedSubcategory ? 'Edit subcategory' : 'Add subcategory'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        selectedSubcategory
                            ? 'Update the subcategory information below.'
                            : 'Enter the new subcategory information below.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Form
                :key="selectedSubcategory?.id ?? 'create'"
                method="post"
                :action="
                    selectedSubcategory
                        ? `/subcategories/${selectedSubcategory.id}`
                        : '/subcategories'
                "
                class="grid min-w-0 gap-5 [&_input]:focus-visible:ring-1 [&_input]:focus-visible:ring-ring/20 [&_select]:focus-visible:ring-1 [&_select]:focus-visible:ring-ring/20"
                :reset-on-success="!selectedSubcategory"
                v-slot="{ errors, processing }"
                @success="formOpen = false"
            >
                <input
                    type="hidden"
                    name="status"
                    :value="selectedSubcategory?.status ?? 1"
                />

                <div class="grid gap-2">
                    <Label for="subcategory_category">Category</Label>
                    <select
                        id="subcategory_category"
                        name="category_id"
                        :value="
                            selectedSubcategory?.category_id ??
                            categories[0]?.id ??
                            ''
                        "
                        class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        required
                    >
                        <option value="" disabled>Select category</option>
                        <option
                            v-for="category in categories"
                            :key="category.id"
                            :value="category.id"
                        >
                            {{ category.name }}
                        </option>
                    </select>
                    <InputError :message="errors.category_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="subcategory_name">Name</Label>
                    <Input
                        id="subcategory_name"
                        name="name"
                        :default-value="selectedSubcategory?.name"
                        placeholder="Enter subcategory name"
                        required
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="subcategory_icon">Icon</Label>
                    <div
                        class="flex items-center gap-4 rounded-md border bg-muted/20 p-3"
                    >
                        <div
                            class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-md border bg-background text-muted-foreground"
                        >
                            <img
                                v-if="displayedIconUrl"
                                :src="displayedIconUrl"
                                :alt="`${selectedSubcategory?.name ?? 'Subcategory'} icon preview`"
                                class="size-full object-cover"
                            />
                            <ImageIcon v-else class="size-6" />
                        </div>

                        <div class="min-w-0 flex-1 space-y-2">
                            <div>
                                <p class="truncate text-sm font-medium">
                                    {{
                                        iconFileName ||
                                        (selectedSubcategory?.icon_url
                                            ? 'Current subcategory icon'
                                            : 'No icon selected')
                                    }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    PNG, JPG or WebP
                                </p>
                            </div>
                            <Label
                                for="subcategory_icon"
                                class="inline-flex h-8 cursor-pointer items-center gap-2 rounded-md border bg-background px-3 text-xs font-medium shadow-xs transition-colors hover:bg-accent hover:text-accent-foreground"
                            >
                                <Upload class="size-3.5" />
                                Choose image
                            </Label>
                        </div>
                        <Input
                            id="subcategory_icon"
                            type="file"
                            name="icon"
                            accept="image/*"
                            class="sr-only"
                            @change="handleIconChange"
                        />
                    </div>
                    <InputError :message="errors.icon" />
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
                        {{ selectedSubcategory ? 'Update' : 'Submit' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="deleteOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete subcategory</DialogTitle>
                <DialogDescription>
                    Delete {{ selectedSubcategory?.name }}? This subcategory
                    will no longer appear in the subcategory list.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="deleteOpen = false"
                    >Cancel</Button
                >
                <Button
                    variant="destructive"
                    :disabled="deleting"
                    @click="deleteSubcategory"
                    >Delete</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
