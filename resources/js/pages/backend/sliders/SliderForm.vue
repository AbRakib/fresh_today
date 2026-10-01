<script setup lang="ts">
import { Form, router } from '@inertiajs/vue3';
import { ChevronsUpDown } from '@lucide/vue';
import {
    ComboboxAnchor,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxPortal,
    ComboboxRoot,
    ComboboxTrigger,
    ComboboxViewport,
} from 'reka-ui';
import { nextTick, ref } from 'vue';
import ImageCropInput from '@/components/ImageCropInput.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export type ProductOption = {
    id: number;
    name: string;
};

export type SliderFormData = {
    id: number;
    image_url: string | null;
    product_id: number | null;
    button: string | null;
    status: number;
};

const props = defineProps<{
    products: ProductOption[];
    slider?: SliderFormData;
}>();

const productId = ref<number | string>(props.slider?.product_id ?? '');
const imagePreviewUrl = ref<string | null>(props.slider?.image_url ?? null);
const submitAttempted = ref(false);

const handleImageCropped = (_file: File, previewUrl: string) => {
    imagePreviewUrl.value = previewUrl;
};

const handleFormError = async (errors: Record<string, unknown>) => {
    submitAttempted.value = true;
    await nextTick();

    const firstError = Object.keys(errors)[0];

    if (!firstError) {
        return;
    }

    const field = document.querySelector<HTMLElement>(`[name="${firstError}"]`);
    const target =
        firstError === 'image'
            ? document.querySelector<HTMLElement>('label[for="slider_image"]')
            : field;

    target?.scrollIntoView({ behavior: 'smooth', block: 'center' });

    if (firstError !== 'image') {
        field?.focus({ preventScroll: true });
    }
};
</script>

<template>
    <Form
        method="post"
        :action="slider ? `/sliders/${slider.id}` : '/sliders'"
        novalidate
        class="space-y-6 [&_input:focus]:!ring-1 [&_input:focus]:!ring-ring/20 [&_select:focus]:border-ring [&_select:focus]:ring-1 [&_select:focus]:ring-ring/20 [&_select:focus]:outline-none"
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
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-1.5 sm:col-span-2">
                            <Label for="slider_product">Product</Label>
                            <ComboboxRoot
                                v-if="!slider"
                                v-model="productId"
                                class="relative min-w-0"
                                :open-on-focus="true"
                            >
                                <input
                                    type="hidden"
                                    name="product_id"
                                    :value="productId"
                                />
                                <ComboboxAnchor
                                    class="flex h-9 items-center rounded-md border border-input bg-background"
                                >
                                    <ComboboxInput
                                        id="slider_product"
                                        :display-value="
                                            (value) =>
                                                products.find(
                                                    (product) =>
                                                        product.id === value,
                                                )?.name ?? ''
                                        "
                                        placeholder="Search products..."
                                        aria-label="Product"
                                        class="h-full min-w-0 flex-1 rounded-md bg-transparent px-3 text-sm outline-none"
                                    />
                                    <ComboboxTrigger
                                        type="button"
                                        aria-label="Show products"
                                        class="flex h-9 w-9 shrink-0 items-center justify-center"
                                    >
                                        <ChevronsUpDown
                                            class="size-4 text-muted-foreground"
                                        />
                                    </ComboboxTrigger>
                                </ComboboxAnchor>
                                <ComboboxPortal>
                                    <ComboboxContent
                                        position="popper"
                                        :side-offset="4"
                                        class="z-50 w-[var(--reka-combobox-trigger-width)] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md"
                                    >
                                        <ComboboxViewport
                                            class="max-h-60 overflow-y-auto p-1"
                                        >
                                            <ComboboxEmpty
                                                class="px-3 py-2 text-sm text-muted-foreground"
                                                >No products
                                                found.</ComboboxEmpty
                                            >
                                            <ComboboxItem
                                                value=""
                                                class="cursor-pointer rounded-sm px-3 py-2 text-sm data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground"
                                                >No linked product</ComboboxItem
                                            >
                                            <ComboboxItem
                                                v-for="product in products"
                                                :key="product.id"
                                                :value="product.id"
                                                :text-value="product.name"
                                                class="cursor-pointer rounded-sm px-3 py-2 text-sm break-words data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground"
                                            >
                                                {{ product.name }}
                                            </ComboboxItem>
                                        </ComboboxViewport>
                                    </ComboboxContent>
                                </ComboboxPortal>
                            </ComboboxRoot>
                            <select
                                v-else
                                id="slider_product"
                                v-model="productId"
                                name="product_id"
                                class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                            >
                                <option value="">No linked product</option>
                                <option
                                    v-for="product in products"
                                    :key="product.id"
                                    :value="product.id"
                                >
                                    {{ product.name }}
                                </option>
                            </select>
                            <InputError :message="errors.product_id" />
                        </div>

                        <div
                            class="grid gap-1.5"
                            :class="{ 'sm:col-span-2': !slider }"
                        >
                            <Label for="slider_button">Button</Label>
                            <Input
                                id="slider_button"
                                name="button"
                                :default-value="slider?.button ?? ''"
                                placeholder="Shop now"
                            />
                            <InputError :message="errors.button" />
                        </div>

                        <div v-if="slider" class="grid gap-1.5">
                            <Label for="slider_status">Status</Label>
                            <select
                                id="slider_status"
                                name="status"
                                :value="slider?.status ?? 1"
                                class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                            >
                                <option :value="1">Active</option>
                                <option :value="0">Inactive</option>
                            </select>
                            <InputError :message="errors.status" />
                        </div>
                        <input v-else type="hidden" name="status" value="1" />
                    </div>
                </div>

                <div
                    class="grid content-start rounded-md border p-4 lg:col-span-2"
                >
                    <ImageCropInput
                        id="slider_image"
                        name="image"
                        label="Slider image"
                        :current-url="slider?.image_url"
                        current-label="Current slider image"
                        empty-label=""
                        choose-label="Choose image"
                        :aspect-ratio="16 / 7"
                        :output-width="1600"
                        :output-height="700"
                        preview-class="aspect-[16/7] w-full"
                        :required="!slider"
                        :invalid="
                            submitAttempted && !slider && !imagePreviewUrl
                        "
                        :error="errors.image"
                        @cropped="handleImageCropped"
                    />
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <Button
                type="button"
                variant="outline"
                @click="router.visit('/sliders')"
            >
                Cancel
            </Button>
            <Button type="submit" :disabled="processing">
                {{
                    processing
                        ? 'Saving...'
                        : slider
                          ? 'Save changes'
                          : 'Submit'
                }}
            </Button>
        </div>
    </Form>
</template>

<style scoped>
.show-required-errors :deep(input:required:invalid),
.show-required-errors :deep(select:required:invalid) {
    border-color: var(--destructive) !important;
}

.show-required-errors :deep(input:required:invalid:focus),
.show-required-errors :deep(select:required:invalid:focus) {
    outline: none;
    box-shadow: 0 0 0 1px
        color-mix(in oklab, var(--destructive) 25%, transparent) !important;
}
</style>
