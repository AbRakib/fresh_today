<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import CompanySettingController from '@/actions/App/Http/Controllers/Settings/CompanySettingController';
import Heading from '@/components/Heading.vue';
import ImageCropInput from '@/components/ImageCropInput.vue';
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

            <div class="grid gap-5 md:grid-cols-2">
                <div class="rounded-md border p-4">
                    <ImageCropInput
                        id="logo"
                        name="logo"
                        label="Logo"
                        helper="PNG, JPG or WebP, up to 2 MB"
                        :current-url="setting.logo_url"
                        current-label="Current company logo"
                        empty-label="No logo selected"
                        choose-label="Choose logo"
                        :show-status="false"
                        :aspect-ratio="null"
                        :output-width="1200"
                        :output-height="675"
                        preview-fit="contain"
                        preview-class="h-32 w-full"
                        :error="errors.logo"
                    />
                </div>

                <div class="rounded-md border p-4">
                    <ImageCropInput
                        id="meta_icon"
                        name="meta_icon"
                        label="Meta icon"
                        helper="Square PNG, JPG or WebP, up to 1 MB"
                        :current-url="setting.meta_icon_url"
                        current-label="Current meta icon"
                        empty-label="No icon selected"
                        choose-label="Choose icon"
                        :show-status="false"
                        :aspect-ratio="1"
                        :output-width="512"
                        :output-height="512"
                        preview-fit="contain"
                        preview-class="h-32 w-full"
                        :error="errors.meta_icon"
                    />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing">Save</Button>
            </div>
        </Form>
    </div>
</template>
