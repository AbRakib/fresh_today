<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ImageIcon, Upload } from '@lucide/vue';
import { onBeforeUnmount, ref } from 'vue';
import CompanySettingController from '@/actions/App/Http/Controllers/Settings/CompanySettingController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/company';

type CompanySetting = {
    country_id: number | null;
    company_name: string;
    email: string;
    phone: string;
    address: string;
    logo: string | null;
    meta_icon: string | null;
    logo_url: string | null;
    meta_icon_url: string | null;
};

type Country = {
    id: number;
    name: string;
    phone_code: string;
    currency: string;
    currency_symbol: string;
};

const { countries, setting } = defineProps<{
    setting: CompanySetting;
    countries: Country[];
}>();

const logoPreview = ref<string | null>(setting.logo_url);
const metaIconPreview = ref<string | null>(setting.meta_icon_url);
const logoFileName = ref<string | null>(null);
const metaIconFileName = ref<string | null>(null);

const previewUrls: string[] = [];

const previewImage = (event: Event, target: 'logo' | 'meta_icon') => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
        return;
    }

    const previewUrl = URL.createObjectURL(file);
    previewUrls.push(previewUrl);

    if (target === 'logo') {
        logoPreview.value = previewUrl;
        logoFileName.value = file.name;

        return;
    }

    metaIconPreview.value = previewUrl;
    metaIconFileName.value = file.name;
};

onBeforeUnmount(() => {
    previewUrls.forEach((url) => URL.revokeObjectURL(url));
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Company settings',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Company settings" />

    <h1 class="sr-only">Company settings</h1>

    <div
        class="mx-auto mt-4 w-full max-w-3xl space-y-6 rounded-md border border-dashed px-4 py-6"
    >
        <Heading
            variant="small"
            title="Company settings"
            description="Update your company contact information and brand assets"
        />

        <Form
            v-bind="CompanySettingController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="company_name">Company name</Label>
                <Input
                    id="company_name"
                    name="company_name"
                    :default-value="setting.company_name"
                    required
                    autocomplete="organization"
                    placeholder="Company name"
                />
                <InputError class="mt-2" :message="errors.company_name" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="country_id">Country</Label>
                    <select
                        id="country_id"
                        name="country_id"
                        :value="setting.country_id ?? ''"
                        class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-1 focus-visible:ring-ring/20"
                    >
                        <option value="">Select country</option>
                        <option
                            v-for="country in countries"
                            :key="country.id"
                            :value="country.id"
                        >
                            {{ country.name }} ({{ country.currency_symbol }}
                            {{ country.currency }})
                        </option>
                    </select>
                    <InputError class="mt-2" :message="errors.country_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        :default-value="setting.email"
                        autocomplete="email"
                        placeholder="company@example.com"
                    />
                    <InputError class="mt-2" :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="phone">Phone</Label>
                    <Input
                        id="phone"
                        name="phone"
                        :default-value="setting.phone"
                        autocomplete="tel"
                        placeholder="+880 1XXX XXXXXX"
                    />
                    <InputError class="mt-2" :message="errors.phone" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="address">Address</Label>
                <textarea
                    id="address"
                    name="address"
                    rows="4"
                    class="flex min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-1 focus-visible:ring-ring/25 disabled:cursor-not-allowed disabled:opacity-50"
                    autocomplete="street-address"
                    placeholder="Company address"
                    :default-value="setting.address"
                />
                <InputError class="mt-2" :message="errors.address" />
            </div>

            <div class="space-y-5">
                <div
                    class="grid gap-3 rounded-md border p-4 sm:grid-cols-[minmax(0,1fr)_12rem] sm:items-center"
                >
                    <div class="min-w-0 space-y-3">
                        <div>
                            <Label for="logo">Logo</Label>
                            <p class="mt-1 text-xs text-muted-foreground">
                                PNG, JPG or WebP, up to 2 MB
                            </p>
                        </div>
                        <p class="truncate text-sm font-medium">
                            {{
                                logoFileName ||
                                (setting.logo
                                    ? 'Current company logo'
                                    : 'No logo selected')
                            }}
                        </p>
                        <Label
                            for="logo"
                            class="inline-flex h-9 w-fit cursor-pointer items-center gap-2 rounded-md border bg-background px-3 text-sm font-medium shadow-xs transition-colors hover:bg-accent hover:text-accent-foreground"
                        >
                            <Upload class="size-4" />
                            Choose logo
                        </Label>
                        <Input
                            id="logo"
                            type="file"
                            name="logo"
                            accept="image/*"
                            class="sr-only"
                            @change="previewImage($event, 'logo')"
                        />
                        <InputError :message="errors.logo" />
                    </div>
                    <div
                        class="flex h-32 w-full items-center justify-center overflow-hidden rounded-md border bg-muted/30 p-3"
                    >
                        <img
                            v-if="logoPreview"
                            :src="logoPreview"
                            alt="Company logo preview"
                            class="max-h-full max-w-full object-contain"
                        />
                        <div
                            v-else
                            class="flex flex-col items-center gap-2 text-muted-foreground"
                        >
                            <ImageIcon class="size-7" />
                            <span class="text-xs">Logo preview</span>
                        </div>
                    </div>
                </div>

                <div
                    class="grid gap-3 rounded-md border p-4 sm:grid-cols-[minmax(0,1fr)_12rem] sm:items-center"
                >
                    <div class="min-w-0 space-y-3">
                        <div>
                            <Label for="meta_icon">Meta icon</Label>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Square PNG, JPG or WebP, up to 1 MB
                            </p>
                        </div>
                        <p class="truncate text-sm font-medium">
                            {{
                                metaIconFileName ||
                                (setting.meta_icon
                                    ? 'Current meta icon'
                                    : 'No icon selected')
                            }}
                        </p>
                        <Label
                            for="meta_icon"
                            class="inline-flex h-9 w-fit cursor-pointer items-center gap-2 rounded-md border bg-background px-3 text-sm font-medium shadow-xs transition-colors hover:bg-accent hover:text-accent-foreground"
                        >
                            <Upload class="size-4" />
                            Choose icon
                        </Label>
                        <Input
                            id="meta_icon"
                            type="file"
                            name="meta_icon"
                            accept="image/*"
                            class="sr-only"
                            @change="previewImage($event, 'meta_icon')"
                        />
                        <InputError :message="errors.meta_icon" />
                    </div>
                    <div
                        class="flex h-32 w-full items-center justify-center overflow-hidden rounded-md border bg-muted/30 p-3"
                    >
                        <img
                            v-if="metaIconPreview"
                            :src="metaIconPreview"
                            alt="Meta icon preview"
                            class="max-h-full max-w-full object-contain"
                        />
                        <div
                            v-else
                            class="flex flex-col items-center gap-2 text-muted-foreground"
                        >
                            <ImageIcon class="size-7" />
                            <span class="text-xs">Icon preview</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing">Save</Button>
            </div>
        </Form>
    </div>
</template>
