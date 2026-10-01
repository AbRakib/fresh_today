<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { FileText, MoreVertical, Pencil, Plus, Search, Trash2 } from '@lucide/vue';
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

type Page = {
    id: number;
    title: string;
    slug: string;
    eyebrow: string | null;
    intro: string | null;
    sections: string;
    section_count: number;
    status: number;
    created_at: string | null;
};

const { pages } = defineProps<{ pages: Page[] }>();

const search = ref('');
const formOpen = ref(false);
const deleteOpen = ref(false);
const selectedPage = ref<Page | null>(null);
const deleting = ref(false);

const filteredPages = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return pages;
    }

    return pages.filter((page) =>
        [page.title, page.slug, page.eyebrow, page.intro, page.sections]
            .filter(Boolean)
            .some((value) => value!.toLowerCase().includes(query)),
    );
});

const openCreate = () => {
    selectedPage.value = null;
    formOpen.value = true;
};

const openEdit = (page: Page) => {
    selectedPage.value = page;
    formOpen.value = true;
};

const openDelete = (page: Page) => {
    selectedPage.value = page;
    deleteOpen.value = true;
};

const deletePage = () => {
    if (!selectedPage.value) {
        return;
    }

    deleting.value = true;
    router.delete(`/pages/${selectedPage.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteOpen.value = false;
            selectedPage.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
};

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Pages', href: '/pages' }],
    },
});
</script>

<template>
    <Head title="Pages" />

    <div class="flex h-full flex-1 flex-col gap-2 p-4 md:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative max-w-sm">
                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input v-model="search" class="pl-9" placeholder="Search pages" aria-label="Search pages" />
            </div>
            <Button class="shrink-0" @click="openCreate">
                <Plus class="size-4" />
                Add page
            </Button>
        </div>

        <div class="overflow-hidden rounded-md border">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] table-fixed text-sm">
                    <colgroup>
                        <col class="w-[6%]" />
                        <col class="w-[32%]" />
                        <col class="w-[20%]" />
                        <col class="w-[12%]" />
                        <col class="w-[12%]" />
                        <col class="w-[10%]" />
                        <col class="w-[8%]" />
                    </colgroup>
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">SL</th>
                            <th class="px-4 py-3 font-medium">Page</th>
                            <th class="px-4 py-3 font-medium">Slug</th>
                            <th class="px-4 py-3 font-medium">Sections</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Created</th>
                            <th class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="(page, index) in filteredPages" :key="page.id" class="hover:bg-muted/30">
                            <td class="px-4 py-3 text-muted-foreground">{{ index + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground">
                                        <FileText class="size-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate font-medium">{{ page.title }}</div>
                                        <div class="truncate text-muted-foreground">{{ page.intro }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">/{{ page.slug }}</td>
                            <td class="px-4 py-3 tabular-nums">{{ page.section_count }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded px-2 py-0.5 text-xs font-medium"
                                    :class="page.status ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-muted text-muted-foreground'"
                                >
                                    {{ page.status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ formatDate(page.created_at) || 'Not available' }}</td>
                            <td class="px-4 py-3">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="ml-auto flex" title="Page actions">
                                            <MoreVertical class="size-4" />
                                            <span class="sr-only">Page actions</span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem @click="openEdit(page)">
                                            <Pencil class="size-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem variant="destructive" @click="openDelete(page)">
                                            <Trash2 class="size-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="filteredPages.length === 0">
                            <td colspan="7" class="px-4 py-12 text-center text-muted-foreground">
                                <FileText class="mx-auto mb-3 size-8" />
                                No pages found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <Dialog v-model:open="formOpen">
        <DialogContent class="max-h-[calc(100vh-2rem)] gap-5 overflow-x-hidden overflow-y-auto p-6" style="width: min(760px, calc(100vw - 2rem)); max-width: 760px">
            <DialogHeader class="gap-1.5 pr-6">
                <DialogTitle>{{ selectedPage ? 'Edit page' : 'Add page' }}</DialogTitle>
                <DialogDescription>
                    Use one blank line between sections. The first line is the section title; following lines are section points.
                </DialogDescription>
            </DialogHeader>

            <Form
                :key="selectedPage?.id ?? 'create'"
                method="post"
                :action="selectedPage ? `/pages/${selectedPage.id}` : '/pages'"
                class="grid min-w-0 gap-5 [&_input]:focus-visible:ring-1 [&_input]:focus-visible:ring-ring/20"
                :reset-on-success="!selectedPage"
                v-slot="{ errors, processing }"
                @success="formOpen = false"
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="page_title">Title</Label>
                        <Input id="page_title" name="title" :default-value="selectedPage?.title" placeholder="e.g. FAQ" required />
                        <div class="min-h-5"><InputError :message="errors.title" /></div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="page_slug">Slug</Label>
                        <Input id="page_slug" name="slug" :default-value="selectedPage?.slug" placeholder="e.g. faq" pattern="[a-z0-9]+(-[a-z0-9]+)*" required />
                        <div class="min-h-5"><InputError :message="errors.slug" /></div>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="page_eyebrow">Eyebrow</Label>
                    <Input id="page_eyebrow" name="eyebrow" :default-value="selectedPage?.eyebrow" placeholder="e.g. Common Questions" />
                    <div class="min-h-5"><InputError :message="errors.eyebrow" /></div>
                </div>

                <div class="grid gap-2">
                    <Label for="page_intro">Intro</Label>
                    <textarea
                        id="page_intro"
                        name="intro"
                        :default-value="selectedPage?.intro"
                        placeholder="Short page introduction"
                        class="flex min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-colors outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/20 disabled:cursor-not-allowed disabled:opacity-50"
                    />
                    <div class="min-h-5"><InputError :message="errors.intro" /></div>
                </div>

                <div class="grid gap-2">
                    <Label for="page_sections">Sections</Label>
                    <textarea
                        id="page_sections"
                        name="sections"
                        :default-value="selectedPage?.sections"
                        placeholder="Ordering&#10;Customers can order from the shop page.&#10;Stock may change daily.&#10;&#10;Delivery&#10;Delivery charges are confirmed during checkout."
                        class="flex min-h-56 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-colors outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/20 disabled:cursor-not-allowed disabled:opacity-50"
                        required
                    />
                    <div class="min-h-5"><InputError :message="errors.sections" /></div>
                </div>

                <div class="grid gap-2 sm:max-w-xs">
                    <Label for="page_status">Status</Label>
                    <select
                        id="page_status"
                        name="status"
                        :value="selectedPage?.status ?? 1"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/20"
                    >
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <div class="min-h-5"><InputError :message="errors.status" /></div>
                </div>

                <DialogFooter class="border-t pt-4">
                    <Button type="button" variant="outline" class="cursor-pointer" @click="formOpen = false">Cancel</Button>
                    <Button type="submit" class="cursor-pointer" :disabled="processing">{{ selectedPage ? 'Update' : 'Submit' }}</Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="deleteOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete page</DialogTitle>
                <DialogDescription>
                    Delete {{ selectedPage?.title }}? This page will no longer be available on the website.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="deleteOpen = false">Cancel</Button>
                <Button variant="destructive" :disabled="deleting" @click="deletePage">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
