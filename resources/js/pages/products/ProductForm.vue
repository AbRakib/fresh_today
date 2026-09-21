<script setup lang="ts">
import { Form, router } from '@inertiajs/vue3';
import { ImageIcon } from '@lucide/vue';
import { computed, nextTick, onBeforeUnmount, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import TextEditor from '@/components/TextEditor.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export type Category = { id: number; name: string };
export type Subcategory = { id: number; category_id: number; name: string };
export type Unit = {
    id: number;
    name: string;
    short_name: string;
    default: number;
};
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
    unit_id: number | null;
    gross_weight: string | null;
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
    units: Unit[];
    product?: ProductFormData;
    defaultSku?: string;
    showProductStateFields?: boolean;
}>();

const categoryId = ref<number | string>(
    props.product?.category_id ?? props.categories[0]?.id ?? '',
);
const subcategoryId = ref<number | string>(props.product?.subcategory_id ?? '');
const unitId = ref<number | string>(
    props.product?.unit_id ??
        props.units.find((unit) => unit.default)?.id ??
        props.units[0]?.id ??
        '',
);
const thumbnailPreviewUrl = ref<string | null>(null);
const submitAttempted = ref(false);
const shortDescription = ref(props.product?.short_description ?? '');
const description = ref(props.product?.description ?? '');

const filteredSubcategories = computed(() =>
    props.subcategories.filter(
        (subcategory) =>
            String(subcategory.category_id) === String(categoryId.value),
    ),
);
const displayedThumbnailUrl = computed(
    () => thumbnailPreviewUrl.value ?? props.product?.thumbnail_url ?? null,
);
const showProductStateFields = computed(
    () => props.showProductStateFields ?? true,
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
};

const handleFormError = async (errors: Record<string, unknown>) => {
    submitAttempted.value = true;
    await nextTick();

    const firstError = Object.keys(errors)[0];
    if (!firstError) return;

    const field = document.querySelector<HTMLElement>(`[name="${firstError}"]`);
    const target =
        firstError === 'thumbnail'
            ? document.querySelector<HTMLElement>(
                  'label[for="product_thumbnail"]',
              )
            : field;

    target?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    if (firstError !== 'thumbnail') field?.focus({ preventScroll: true });
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
        novalidate
        class="space-y-6 [&_input:focus]:!ring-1 [&_input:focus]:!ring-ring/25 [&_select:focus]:border-ring [&_select:focus]:ring-1 [&_select:focus]:ring-ring/25 [&_select:focus]:outline-none [&_textarea:focus]:border-ring [&_textarea:focus]:ring-1 [&_textarea:focus]:ring-ring/25 [&_textarea:focus]:outline-none"
        :class="{ 'show-required-errors': submitAttempted }"
        v-slot="{ errors, processing }"
        @invalid.capture="submitAttempted = true"
        @submit.capture="submitAttempted = true"
        @error="handleFormError"
    >
        <div class="space-y-5 rounded-md border p-4 sm:p-5">
            <div class="grid gap-5 lg:grid-cols-5">
                <div
                    class="grid content-start gap-4 rounded-md border p-4 lg:col-span-3"
                    :class="{ 'border-dashed': !showProductStateFields }"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-1.5 sm:col-span-2">
                            <Label for="product_name"
                                >Name
                                <span
                                    class="text-destructive"
                                    aria-hidden="true"
                                    >*</span
                                ></Label
                            >
                            <Input
                                id="product_name"
                                name="name"
                                :default-value="product?.name"
                                placeholder="Enter product name"
                                required
                            />
                            <InputError :message="errors.name" />
                        </div>
                        <div class="grid gap-1.5 sm:col-span-2">
                            <Label for="product_short_description"
                                >Short description
                                <span
                                    class="text-destructive"
                                    aria-hidden="true"
                                    >*</span
                                ></Label
                            >
                            <textarea
                                id="product_short_description"
                                v-model="shortDescription"
                                name="short_description"
                                rows="3"
                                maxlength="1000"
                                placeholder="Brief product summary"
                                class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                                required
                            />
                            <InputError :message="errors.short_description" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="product_sku"
                                >SKU
                                <span
                                    class="text-destructive"
                                    aria-hidden="true"
                                    >*</span
                                ></Label
                            >
                            <Input
                                id="product_sku"
                                type="number"
                                min="1"
                                step="1"
                                name="sku"
                                :default-value="
                                    product?.sku ?? defaultSku ?? '1001'
                                "
                                placeholder="1001"
                                required
                            />
                            <InputError :message="errors.sku" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="product_unit"
                                >Unit
                                <span
                                    class="text-destructive"
                                    aria-hidden="true"
                                    >*</span
                                ></Label
                            >
                            <select
                                id="product_unit"
                                v-model="unitId"
                                name="unit_id"
                                class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                required
                            >
                                <option value="">Select unit</option>
                                <option
                                    v-for="unit in units"
                                    :key="unit.id"
                                    :value="unit.id"
                                >
                                    {{ unit.name }} ({{ unit.short_name }})
                                </option>
                            </select>
                            <InputError :message="errors.unit_id" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="product_category"
                                >Category
                                <span
                                    class="text-destructive"
                                    aria-hidden="true"
                                    >*</span
                                ></Label
                            >
                            <select
                                id="product_category"
                                v-model="categoryId"
                                name="category_id"
                                class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                required
                                @change="changeCategory"
                            >
                                <option value="" disabled>
                                    Select category
                                </option>
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
                                <option value="">Select subcategory</option>
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
                </div>

                <div
                    class="grid content-start gap-3 rounded-md border p-4 lg:col-span-2"
                    :class="{ 'border-dashed': !showProductStateFields }"
                >
                    <Label for="product_thumbnail"
                        >Image
                        <span class="text-destructive" aria-hidden="true"
                            >*</span
                        ></Label
                    >
                    <Label
                        for="product_thumbnail"
                        class="flex aspect-square w-full cursor-pointer items-center justify-center overflow-hidden rounded-md border bg-muted/20 text-muted-foreground transition-colors hover:border-primary hover:bg-muted/40"
                        :class="{
                            'border-destructive ring-1 ring-destructive/30':
                                submitAttempted &&
                                !product &&
                                !displayedThumbnailUrl,
                        }"
                    >
                        <img
                            v-if="displayedThumbnailUrl"
                            :src="displayedThumbnailUrl"
                            alt="Product thumbnail preview"
                            class="size-full object-cover"
                        />
                        <ImageIcon v-else class="size-10" />
                    </Label>
                    <Input
                        id="product_thumbnail"
                        type="file"
                        name="thumbnail"
                        accept="image/*"
                        class="sr-only"
                        :required="!product"
                        @change="handleThumbnailChange"
                    />
                    <InputError :message="errors.thumbnail" />
                </div>
            </div>

            <div
                class="grid gap-4 rounded-md border p-4"
                :class="{ 'border-dashed': !showProductStateFields }"
            >
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="grid gap-1.5">
                        <Label for="product_regular_price"
                            >Regular price
                            <span class="text-destructive" aria-hidden="true"
                                >*</span
                            ></Label
                        >
                        <Input
                            id="product_regular_price"
                            type="number"
                            step="0.01"
                            min="0"
                            name="regular_price"
                            :default-value="product?.regular_price ?? '0'"
                            placeholder="0.00"
                            required
                        />
                        <InputError :message="errors.regular_price" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="product_sale_price"
                            >Sell price
                            <span class="text-destructive" aria-hidden="true"
                                >*</span
                            ></Label
                        >
                        <Input
                            id="product_sale_price"
                            type="number"
                            step="0.01"
                            min="0"
                            name="sale_price"
                            :default-value="product?.sale_price ?? ''"
                            placeholder="0.00"
                            required
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
                            placeholder="10"
                        />
                        <InputError :message="errors.discount_percentage" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="grid gap-1.5">
                        <Label for="product_gross_weight"
                            >Gross weight
                            <span class="text-destructive" aria-hidden="true"
                                >*</span
                            ></Label
                        >
                        <Input
                            id="product_gross_weight"
                            name="gross_weight"
                            :default-value="product?.gross_weight ?? ''"
                            placeholder="1 kg"
                            required
                        />
                        <InputError :message="errors.gross_weight" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="product_weight"
                            >Weight
                            <span class="text-destructive" aria-hidden="true"
                                >*</span
                            ></Label
                        >
                        <Input
                            id="product_weight"
                            name="weight"
                            :default-value="product?.weight ?? ''"
                            placeholder="900 g"
                            required
                        />
                        <InputError :message="errors.weight" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="product_moq"
                            >Minimum order
                            <span class="text-destructive" aria-hidden="true"
                                >*</span
                            ></Label
                        >
                        <Input
                            id="product_moq"
                            type="number"
                            min="1"
                            name="minimum_order_quantity"
                            :default-value="
                                product?.minimum_order_quantity ?? 1
                            "
                            placeholder="1"
                            required
                        />
                        <InputError :message="errors.minimum_order_quantity" />
                    </div>
                </div>

                <div v-if="showProductStateFields" class="grid gap-1.5">
                    <Label for="product_stock">Stock</Label>
                    <Input
                        id="product_stock"
                        type="number"
                        min="0"
                        name="stock_quantity"
                        :default-value="product?.stock_quantity ?? 0"
                        placeholder="0"
                        required
                    />
                    <InputError :message="errors.stock_quantity" />
                </div>
                <input
                    v-else
                    type="hidden"
                    name="stock_quantity"
                    :value="product?.stock_quantity ?? 0"
                />
            </div>

            <input
                v-if="!showProductStateFields"
                type="hidden"
                name="is_featured"
                :value="product?.is_featured ?? 0"
            />
            <input
                v-if="!showProductStateFields"
                type="hidden"
                name="status"
                :value="product?.status ?? 1"
            />

            <div
                class="grid gap-4 rounded-md border p-4"
                :class="[
                    showProductStateFields ? 'sm:grid-cols-3' : '',
                    { 'border-dashed': !showProductStateFields },
                ]"
            >
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
                <div v-if="showProductStateFields" class="grid gap-1.5">
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
                <div v-if="showProductStateFields" class="grid gap-1.5">
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

            <div
                class="grid gap-5 rounded-md border p-4"
                :class="{ 'border-dashed': !showProductStateFields }"
            >
                <div class="grid gap-1.5">
                    <Label for="product_description">Description</Label>
                    <TextEditor
                        v-model="description"
                        name="description"
                        placeholder="Detailed product description"
                    />
                    <InputError :message="errors.description" />
                </div>
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
                processing ? 'Saving...' : product ? 'Save changes' : 'Submit'
            }}</Button>
        </div>
    </Form>
</template>

<style scoped>
.show-required-errors :deep(input:required:invalid),
.show-required-errors :deep(select:required:invalid),
.show-required-errors :deep(textarea:required:invalid) {
    border-color: var(--destructive) !important;
}

.show-required-errors :deep(input:required:invalid:focus),
.show-required-errors :deep(select:required:invalid:focus),
.show-required-errors :deep(textarea:required:invalid:focus) {
    outline: none;
    box-shadow: 0 0 0 2px
        color-mix(in oklab, var(--destructive) 30%, transparent) !important;
}
</style>
