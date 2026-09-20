<script setup lang="ts">
import { Form, router } from '@inertiajs/vue3';
import { ImageIcon, Upload } from '@lucide/vue';
import { computed, onBeforeUnmount, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export type Category = { id: number; name: string };
export type Subcategory = { id: number; category_id: number; name: string };
export type ProductFormData = {
    id: number;
    category_id: number;
    subcategory_id: number | null;
    name: string;
    slug: string;
    sku: string | null;
    thumbnail_url: string | null;
    short_description: string | null;
    description: string | null;
    unit: string | null;
    weight: string | null;
    regular_price: string;
    sale_price: string | null;
    discount_percentage: string | null;
    badge: string | null;
    stock_quantity: number;
    minimum_order_quantity: number;
    is_featured: number;
    status: number;
};

const props = defineProps<{
    categories: Category[];
    subcategories: Subcategory[];
    product?: ProductFormData;
}>();

const categoryId = ref<number | string>(
    props.product?.category_id ?? props.categories[0]?.id ?? '',
);
const subcategoryId = ref<number | string>(props.product?.subcategory_id ?? '');
const thumbnailPreviewUrl = ref<string | null>(null);
const thumbnailFileName = ref('');

const filteredSubcategories = computed(() =>
    props.subcategories.filter(
        (subcategory) =>
            String(subcategory.category_id) === String(categoryId.value),
    ),
);
const displayedThumbnailUrl = computed(
    () => thumbnailPreviewUrl.value ?? props.product?.thumbnail_url ?? null,
);

const changeCategory = () => {
    if (
        !filteredSubcategories.value.some(
            (subcategory) =>
                String(subcategory.id) === String(subcategoryId.value),
        )
    ) {
        subcategoryId.value = '';
    }
};

const handleThumbnailChange = (event: Event) => {
    if (thumbnailPreviewUrl.value) {
        URL.revokeObjectURL(thumbnailPreviewUrl.value);
    }

    const file = (event.target as HTMLInputElement).files?.[0];
    thumbnailPreviewUrl.value = file ? URL.createObjectURL(file) : null;
    thumbnailFileName.value = file?.name ?? '';
};

onBeforeUnmount(() => {
    if (thumbnailPreviewUrl.value) {
        URL.revokeObjectURL(thumbnailPreviewUrl.value);
    }
});
</script>

<template>
    <Form
        method="post"
        :action="product ? `/products/${product.id}` : '/products'"
        class="space-y-6"
        v-slot="{ errors, processing }"
    >
        <div class="grid gap-5 rounded-md border p-5 sm:p-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-1.5">
                    <Label for="product_name">Name</Label>
                    <Input
                        id="product_name"
                        name="name"
                        :default-value="product?.name"
                        required
                    />
                    <InputError :message="errors.name" />
                </div>
                <div class="grid gap-1.5">
                    <Label for="product_sku">SKU</Label>
                    <Input
                        id="product_sku"
                        name="sku"
                        :default-value="product?.sku ?? ''"
                    />
                    <InputError :message="errors.sku" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-1.5">
                    <Label for="product_category">Category</Label>
                    <select
                        id="product_category"
                        v-model="categoryId"
                        name="category_id"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        required
                        @change="changeCategory"
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
                <div class="grid gap-1.5">
                    <Label for="product_subcategory">Subcategory</Label>
                    <select
                        id="product_subcategory"
                        v-model="subcategoryId"
                        name="subcategory_id"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                    >
                        <option value="">None</option>
                        <option
                            v-for="subcategory in filteredSubcategories"
                            :key="subcategory.id"
                            :value="subcategory.id"
                        >
                            {{ subcategory.name }}
                        </option>
                    </select>
                    <InputError :message="errors.subcategory_id" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="grid gap-1.5">
                    <Label for="product_regular_price">Regular price</Label>
                    <Input
                        id="product_regular_price"
                        type="number"
                        step="0.01"
                        min="0"
                        name="regular_price"
                        :default-value="product?.regular_price ?? '0'"
                        required
                    />
                    <InputError :message="errors.regular_price" />
                </div>
                <div class="grid gap-1.5">
                    <Label for="product_sale_price">Sale price</Label>
                    <Input
                        id="product_sale_price"
                        type="number"
                        step="0.01"
                        min="0"
                        name="sale_price"
                        :default-value="product?.sale_price ?? ''"
                    />
                    <InputError :message="errors.sale_price" />
                </div>
                <div class="grid gap-1.5">
                    <Label for="product_discount">Discount %</Label>
                    <Input
                        id="product_discount"
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        name="discount_percentage"
                        :default-value="product?.discount_percentage ?? ''"
                    />
                    <InputError :message="errors.discount_percentage" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="grid gap-1.5">
                    <Label for="product_stock">Stock</Label>
                    <Input
                        id="product_stock"
                        type="number"
                        min="0"
                        name="stock_quantity"
                        :default-value="product?.stock_quantity ?? 0"
                        required
                    />
                    <InputError :message="errors.stock_quantity" />
                </div>
                <div class="grid gap-1.5">
                    <Label for="product_moq">Minimum order</Label>
                    <Input
                        id="product_moq"
                        type="number"
                        min="1"
                        name="minimum_order_quantity"
                        :default-value="product?.minimum_order_quantity ?? 1"
                        required
                    />
                    <InputError :message="errors.minimum_order_quantity" />
                </div>
                <div class="grid gap-1.5">
                    <Label for="product_unit">Unit</Label>
                    <Input
                        id="product_unit"
                        name="unit"
                        :default-value="product?.unit ?? ''"
                        placeholder="kg, pcs"
                    />
                    <InputError :message="errors.unit" />
                </div>
                <div class="grid gap-1.5">
                    <Label for="product_weight">Weight</Label>
                    <Input
                        id="product_weight"
                        name="weight"
                        :default-value="product?.weight ?? ''"
                    />
                    <InputError :message="errors.weight" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="grid gap-1.5">
                    <Label for="product_badge">Badge</Label>
                    <Input
                        id="product_badge"
                        name="badge"
                        :default-value="product?.badge ?? ''"
                        placeholder="Fresh, New"
                    />
                    <InputError :message="errors.badge" />
                </div>
                <div class="grid gap-1.5">
                    <Label for="product_featured">Featured</Label>
                    <select
                        id="product_featured"
                        name="is_featured"
                        :value="product?.is_featured ?? 0"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                    >
                        <option :value="0">No</option>
                        <option :value="1">Yes</option>
                    </select>
                    <InputError :message="errors.is_featured" />
                </div>
                <div class="grid gap-1.5">
                    <Label for="product_status">Status</Label>
                    <select
                        id="product_status"
                        name="status"
                        :value="product?.status ?? 1"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                    >
                        <option :value="1">Active</option>
                        <option :value="0">Inactive</option>
                    </select>
                    <InputError :message="errors.status" />
                </div>
            </div>

            <div class="grid gap-1.5">
                <Label for="product_thumbnail">Thumbnail</Label>
                <div
                    class="flex items-center gap-4 rounded-md border bg-muted/20 p-3"
                >
                    <div
                        class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-md border bg-background text-muted-foreground"
                    >
                        <img
                            v-if="displayedThumbnailUrl"
                            :src="displayedThumbnailUrl"
                            alt="Product thumbnail preview"
                            class="size-full object-cover"
                        />
                        <ImageIcon v-else class="size-6" />
                    </div>
                    <div class="min-w-0 flex-1 space-y-2">
                        <div>
                            <p class="truncate text-sm font-medium">
                                {{
                                    thumbnailFileName ||
                                    (product?.thumbnail_url
                                        ? 'Current product thumbnail'
                                        : 'No thumbnail selected')
                                }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                PNG, JPG or WebP
                            </p>
                        </div>
                        <Label
                            for="product_thumbnail"
                            class="inline-flex h-8 cursor-pointer items-center gap-2 rounded-md border bg-background px-3 text-xs font-medium shadow-xs hover:bg-accent"
                        >
                            <Upload class="size-3.5" /> Choose image
                        </Label>
                    </div>
                    <Input
                        id="product_thumbnail"
                        type="file"
                        name="thumbnail"
                        accept="image/*"
                        class="sr-only"
                        @change="handleThumbnailChange"
                    />
                </div>
                <InputError :message="errors.thumbnail" />
            </div>

            <div class="grid gap-1.5">
                <Label for="product_short_description">Short description</Label>
                <textarea
                    id="product_short_description"
                    name="short_description"
                    rows="3"
                    :value="product?.short_description ?? ''"
                    class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                />
                <InputError :message="errors.short_description" />
            </div>
            <div class="grid gap-1.5">
                <Label for="product_description">Description</Label>
                <textarea
                    id="product_description"
                    name="description"
                    rows="6"
                    :value="product?.description ?? ''"
                    class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                />
                <InputError :message="errors.description" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <Button
                type="button"
                variant="outline"
                @click="router.visit('/products')"
                >Cancel</Button
            >
            <Button type="submit" :disabled="processing">{{
                processing
                    ? 'Saving...'
                    : product
                      ? 'Save changes'
                      : 'Create product'
            }}</Button>
        </div>
    </Form>
</template>
