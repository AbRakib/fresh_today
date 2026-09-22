<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Camera, Upload } from '@lucide/vue';
import { computed, onBeforeUnmount, ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useInitials } from '@/composables/useInitials';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const { getInitials } = useInitials();
const photoPreview = ref<string | null>(null);
const photoFileName = ref('');

const displayedPhoto = computed(() => photoPreview.value ?? user.value.avatar);

const previewPhoto = (event: Event) => {
    if (photoPreview.value) {
        URL.revokeObjectURL(photoPreview.value);
    }

    const file = (event.target as HTMLInputElement).files?.[0];
    photoPreview.value = file ? URL.createObjectURL(file) : null;
    photoFileName.value = file?.name ?? '';
};

onBeforeUnmount(() => {
    if (photoPreview.value) {
        URL.revokeObjectURL(photoPreview.value);
    }
});
</script>

<template>
    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div class="flex">
        <div class="flex w-full max-w-2xl flex-col space-y-6 rounded-lg border border-gray-200 p-10">
            <Heading
                class="text-center"
                variant="small"
                title="Profile"
                description="Update your profile picture, name, and email address"
            />

            <Form
                v-bind="ProfileController.update.form()"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <div class="flex flex-col items-center gap-3 text-center">
                    <Avatar class="size-24 border bg-muted">
                        <AvatarImage
                            v-if="displayedPhoto"
                            :src="displayedPhoto"
                            :alt="user.name + ' profile picture'"
                            class="object-cover"
                        />
                        <AvatarFallback class="text-xl font-medium">
                            {{ getInitials(user.name) }}
                        </AvatarFallback>
                    </Avatar>

                    <div class="space-y-1">
                        <Label
                            for="photo"
                            class="inline-flex h-9 cursor-pointer items-center justify-center gap-2 rounded-md border border-input bg-background px-4 text-sm font-medium shadow-xs transition-colors hover:bg-accent hover:text-accent-foreground"
                        >
                            <Upload class="size-4" />
                            Choose picture
                        </Label>
                        <input
                            id="photo"
                            name="photo"
                            type="file"
                            accept="image/*"
                            class="sr-only"
                            @change="previewPhoto"
                        />
                        <p class="text-xs text-muted-foreground">
                            {{
                                photoFileName || 'JPG, PNG, GIF, or WebP up to 2 MB'
                            }}
                        </p>
                        <InputError :message="errors.photo" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        class="mt-1 block w-full"
                        name="name"
                        :default-value="user.name"
                        required
                        autocomplete="name"
                        placeholder="Full name"
                    />
                    <InputError class="mt-2" :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        name="email"
                        :default-value="user.email"
                        required
                        autocomplete="username"
                        placeholder="Email address"
                    />
                    <InputError class="mt-2" :message="errors.email" />
                </div>

                <div v-if="page.props.mustVerifyEmail && !user.email_verified_at">
                    <p class="-mt-4 text-sm text-muted-foreground">
                        Your email address is unverified.
                        <Link
                            :href="send()"
                            as="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        >
                            Click here to re-send the verification email.
                        </Link>
                    </p>

                    <div
                        v-if="page.props.status === 'verification-link-sent'"
                        class="mt-2 text-sm font-medium text-green-600"
                    >
                        A new verification link has been sent to your email address.
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Button
                        :disabled="processing"
                        data-test="update-profile-button"
                    >
                        <Camera class="size-4" />
                        Save profile
                    </Button>
                </div>
            </Form>
        </div>
    </div>
</template>
