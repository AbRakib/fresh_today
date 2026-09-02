<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';
import CompanySettingController from '@/actions/App/Http/Controllers/Settings/CompanySettingController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/company';

type CompanySetting = {
    company_name: string;
    email: string;
    phone: string;
    address: string;
    logo: string | null;
    meta_icon: string | null;
    logo_url: string | null;
    meta_icon_url: string | null;
};

const { setting } = defineProps<{
    setting: CompanySetting;
}>();

const logoPreview = ref<string | null>(setting.logo_url);
const metaIconPreview = ref<string | null>(setting.meta_icon_url);

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

        return;
    }

    metaIconPreview.value = previewUrl;
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

    <div class="max-w-3xl space-y-6 px-4 py-6">
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
                    class="flex min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                    autocomplete="street-address"
                    placeholder="Company address"
                    :default-value="setting.address"
                />
                <InputError class="mt-2" :message="errors.address" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="logo">Logo</Label>
                    <div
                        v-if="logoPreview"
                        class="flex h-24 items-center rounded-md border bg-muted/30 p-3"
                    >
                        <img
                            :src="logoPreview"
                            alt="Company logo preview"
                            class="max-h-full max-w-full object-contain"
                        />
                    </div>
                    <Input
                        id="logo"
                        type="file"
                        name="logo"
                        accept="image/*"
                        @change="previewImage($event, 'logo')"
                    />
                    <InputError class="mt-2" :message="errors.logo" />
                </div>

                <div class="grid gap-2">
                    <Label for="meta_icon">Meta icon</Label>
                    <div
                        v-if="metaIconPreview"
                        class="flex h-24 items-center rounded-md border bg-muted/30 p-3"
                    >
                        <img
                            :src="metaIconPreview"
                            alt="Meta icon preview"
                            class="max-h-full max-w-full object-contain"
                        />
                    </div>
                    <Input
                        id="meta_icon"
                        type="file"
                        name="meta_icon"
                        accept="image/*"
                        @change="previewImage($event, 'meta_icon')"
                    />
                    <InputError class="mt-2" :message="errors.meta_icon" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing">Save</Button>
            </div>
        </Form>
    </div>
</template>
