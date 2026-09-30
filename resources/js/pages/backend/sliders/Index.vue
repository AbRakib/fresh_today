<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ImageIcon,
    MoreVertical,
    Pencil,
    Plus,
    Search,
    Trash2,
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
import { formatDate } from '@/lib/utils';

type Slider = {
    id: number;
    image_url: string;
    product_id: number | null;
    product_name: string | null;
    button: string | null;
    status: number;
    created_at: string | null;
};

const { sliders } = defineProps<{ sliders: Slider[] }>();

const search = ref('');
const deleteOpen = ref(false);
const selectedSlider = ref<Slider | null>(null);
const deleting = ref(false);

const filteredSliders = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return sliders;
    }

    return sliders.filter((slider) =>
        [slider.product_name, slider.button, String(slider.id)]
            .filter(Boolean)
            .some((value) => value!.toLowerCase().includes(query)),
    );
});

const openDelete = (slider: Slider) => {
    selectedSlider.value = slider;
    deleteOpen.value = true;
};

const deleteSlider = () => {
    if (!selectedSlider.value) {
        return;
    }

    deleting.value = true;
    router.delete(`/sliders/${selectedSlider.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteOpen.value = false;
            selectedSlider.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
};

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Sliders', href: '/sliders' }],
    },
});
</script>

<template>
    <Head title="Sliders" />

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
                    placeholder="Search sliders"
                    aria-label="Search sliders"
                />
            </div>
            <Button class="shrink-0" @click="router.visit('/sliders/create')">
                <Plus class="size-4" />
                Add slider
            </Button>
        </div>

        <div class="overflow-hidden rounded-md border">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50 text-left">
                        <tr>
                            <th class="w-16 px-4 py-3 font-medium">SL</th>
                            <th class="px-4 py-3 font-medium">Image</th>
                            <th class="px-4 py-3 font-medium">Product</th>
                            <th class="px-4 py-3 font-medium">Button</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Created</th>
                            <th class="w-24 px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="(slider, index) in filteredSliders"
                            :key="slider.id"
                            class="hover:bg-muted/30"
                        >
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ index + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <img
                                    :src="slider.image_url"
                                    :alt="`Slider #${slider.id}`"
                                    class="h-16 w-32 rounded-md border object-cover"
                                />
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ slider.product_name || 'Not linked' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ slider.button || 'N/A' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        slider.status
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    {{ slider.status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{
                                    formatDate(slider.created_at) ||
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
                                            title="Slider actions"
                                        >
                                            <MoreVertical class="size-4" />
                                            <span class="sr-only"
                                                >Slider actions</span
                                            >
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            @click="
                                                router.visit(
                                                    `/sliders/${slider.id}/edit`,
                                                )
                                            "
                                        >
                                            <Pencil class="size-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            variant="destructive"
                                            @click="openDelete(slider)"
                                        >
                                            <Trash2 class="size-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="filteredSliders.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <ImageIcon class="mx-auto mb-3 size-8" />
                                No sliders found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <Dialog v-model:open="deleteOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete slider</DialogTitle>
                <DialogDescription>
                    Delete slider #{{ selectedSlider?.id }}? It will no longer
                    appear in the slider list.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="deleteOpen = false">
                    Cancel
                </Button>
                <Button
                    variant="destructive"
                    :disabled="deleting"
                    @click="deleteSlider"
                >
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
