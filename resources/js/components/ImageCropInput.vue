<script setup lang="ts">
import {
    ImageIcon,
    RotateCcw,
    RotateCw,
    Upload,
    ZoomIn,
    ZoomOut,
} from '@lucide/vue';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import { computed, nextTick, onBeforeUnmount, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

type PreviewFit = 'contain' | 'cover';

const props = withDefaults(
    defineProps<{
        id: string;
        name: string;
        label: string;
        accept?: string;
        currentUrl?: string | null;
        currentLabel?: string;
        emptyLabel?: string;
        helper?: string;
        error?: string;
        aspectRatio?: number | null;
        outputWidth?: number;
        outputHeight?: number;
        required?: boolean;
        previewFit?: PreviewFit;
        previewClass?: string;
        chooseLabel?: string;
        invalid?: boolean;
        showHeader?: boolean;
        showStatus?: boolean;
    }>(),
    {
        accept: 'image/*',
        currentUrl: null,
        currentLabel: 'Current image',
        emptyLabel: 'No image selected',
        helper: '',
        error: '',
        aspectRatio: 1,
        outputWidth: 900,
        outputHeight: 900,
        required: false,
        previewFit: 'cover',
        previewClass: 'h-32 w-full',
        chooseLabel: 'Choose image',
        invalid: false,
        showHeader: true,
        showStatus: true,
    },
);

const emit = defineEmits<{
    cropped: [file: File, previewUrl: string];
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const imageElement = ref<HTMLImageElement | null>(null);
const cropOpen = ref(false);
const sourceUrl = ref<string | null>(null);
const previewUrl = ref<string | null>(props.currentUrl);
const selectedFile = ref<File | null>(null);
const acceptedFile = ref<File | null>(null);
const acceptedFileName = ref('');
let cropper: Cropper | null = null;

const displayedLabel = computed(
    () =>
        acceptedFileName.value ||
        (props.currentUrl ? props.currentLabel : props.emptyLabel),
);

const revoke = (url: string | null) => {
    if (url?.startsWith('blob:')) {
        URL.revokeObjectURL(url);
    }
};

const destroyCropper = () => {
    cropper?.destroy();
    cropper = null;
};

const setInputFile = (file: File | null) => {
    if (!fileInput.value) {
        return;
    }

    if (!file) {
        fileInput.value.value = '';

        return;
    }

    const files = new DataTransfer();
    files.items.add(file);
    fileInput.value.files = files.files;
};

const closeCrop = () => {
    destroyCropper();
    setInputFile(acceptedFile.value);
    selectedFile.value = null;
    cropOpen.value = false;
    revoke(sourceUrl.value);
    sourceUrl.value = null;
};

const handleFileChange = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
        return;
    }

    revoke(sourceUrl.value);
    sourceUrl.value = URL.createObjectURL(file);
    selectedFile.value = file;
    cropOpen.value = true;

    await nextTick();
};

const initializeCropper = () => {
    if (!imageElement.value) {
        return;
    }

    destroyCropper();
    cropper = new Cropper(imageElement.value, {
        aspectRatio: props.aspectRatio ?? NaN,
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 1,
        responsive: true,
        restore: false,
        guides: true,
        center: true,
        highlight: false,
        background: false,
        movable: true,
        zoomable: true,
        zoomOnTouch: true,
        zoomOnWheel: true,
    });
};

const cropImage = async () => {
    if (!selectedFile.value || !cropper || !fileInput.value) {
        closeCrop();

        return;
    }

    const canvas =
        props.aspectRatio === null
            ? cropper.getCroppedCanvas({
                  maxWidth: props.outputWidth,
                  maxHeight: props.outputHeight,
                  imageSmoothingEnabled: true,
                  imageSmoothingQuality: 'high',
              })
            : cropper.getCroppedCanvas({
                  width: props.outputWidth,
                  height: props.outputHeight,
                  imageSmoothingEnabled: true,
                  imageSmoothingQuality: 'high',
              });

    if (!canvas) {
        closeCrop();

        return;
    }

    const selectedImage = selectedFile.value;
    const outputType = ['image/jpeg', 'image/png', 'image/webp'].includes(
        selectedImage.type,
    )
        ? selectedImage.type
        : 'image/jpeg';
    const blob = await new Promise<Blob | null>((resolve) =>
        canvas.toBlob(resolve, outputType, 0.92),
    );

    if (!blob) {
        closeCrop();

        return;
    }

    const extension =
        outputType === 'image/png'
            ? 'png'
            : outputType === 'image/webp'
              ? 'webp'
              : 'jpg';
    const file = new File(
        [blob],
        selectedImage.name.replace(/\.[^.]+$/, '') + '-cropped.' + extension,
        { type: blob.type },
    );
    setInputFile(file);
    acceptedFile.value = file;

    revoke(previewUrl.value);
    const nextPreviewUrl = URL.createObjectURL(file);
    previewUrl.value = nextPreviewUrl;
    acceptedFileName.value = file.name;
    selectedFile.value = null;
    cropOpen.value = false;
    emit('cropped', file, nextPreviewUrl);
};

onBeforeUnmount(() => {
    destroyCropper();
    revoke(sourceUrl.value);
    revoke(previewUrl.value);
});
</script>

<template>
    <div class="grid">
        <div class="min-w-0">
            <input
                :id="id"
                ref="fileInput"
                type="file"
                :name="name"
                :accept="accept"
                :required="required"
                class="sr-only"
                @change="handleFileChange"
            />
            <InputError :message="error" />
        </div>

        <Label
            :for="id"
            class="group flex cursor-pointer items-center justify-center overflow-hidden rounded-md border bg-muted/20 text-muted-foreground transition-colors hover:border-primary hover:bg-muted/40"
            :class="[
                previewClass,
                invalid ? 'border-destructive ring-1 ring-destructive/30' : '',
            ]"
        >c
            <img
                v-if="previewUrl"
                :src="previewUrl"
                :alt="`${label} preview`"
                class="size-full"
                :class="
                    previewFit === 'contain'
                        ? 'object-contain p-3'
                        : 'object-cover'
                "
            />
            <div
                v-else
                class="flex flex-col items-center justify-center gap-3 text-center"
            >
                <ImageIcon class="size-8" />
                <span
                    class="inline-flex h-9 items-center gap-2 rounded-md border bg-background px-3 text-sm font-medium text-foreground shadow-xs transition-colors group-hover:bg-accent group-hover:text-accent-foreground"
                >
                    <Upload class="size-4" />
                    {{ chooseLabel }}
                </span>
            </div>
        </Label>
    </div>

    <Dialog
        v-model:open="cropOpen"
        @update:open="(open) => !open && closeCrop()"
    >
        <DialogContent class="sm:max-w-xl">
            <DialogHeader>
                <DialogTitle>Crop {{ label.toLowerCase() }}</DialogTitle>
            </DialogHeader>
            <div class="space-y-4">
                <div
                    class="h-[min(60vh,420px)] w-full overflow-hidden rounded-md border bg-muted"
                >
                    <img
                        v-if="sourceUrl"
                        ref="imageElement"
                        :src="sourceUrl"
                        alt=""
                        class="block max-w-full"
                        draggable="false"
                        @load="initializeCropper"
                    />
                </div>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        title="Zoom out"
                        @click="cropper?.zoom(-0.1)"
                    >
                        <ZoomOut class="size-4" />
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        title="Zoom in"
                        @click="cropper?.zoom(0.1)"
                    >
                        <ZoomIn class="size-4" />
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        title="Rotate left"
                        @click="cropper?.rotate(-90)"
                    >
                        <RotateCcw class="size-4" />
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        title="Rotate right"
                        @click="cropper?.rotate(90)"
                    >
                        <RotateCw class="size-4" />
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        title="Reset crop"
                        @click="cropper?.reset()"
                    >
                        Reset
                    </Button>
                </div>
            </div>
            <DialogFooter>
                <Button type="button" variant="outline" @click="closeCrop">
                    Cancel
                </Button>
                <Button type="button" @click="cropImage">Crop image</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
