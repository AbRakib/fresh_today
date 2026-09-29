<script setup lang="ts">
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    Fish,
    Heart,
    LogOut,
    Mail,
    Phone,
    Search,
    ShoppingCart,
    UserRound,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { dashboard } from '@/routes';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type FrontendCategory = {
    id: number;
    name: string;
    icon_url: string | null;
};

type FrontendCustomer = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    avatar: string | null;
};

const page = usePage<{
    frontend_categories?: FrontendCategory[];
    customer_auth_modal?: 'login' | 'register' | null;
    wishlist_count?: number;
    cart_count?: number;
    auth: {
        user: unknown | null;
        customer: FrontendCustomer | null;
    };
}>();

const frontendCategories = computed(() => page.props.frontend_categories ?? []);
const wishlistCount = computed(() => page.props.wishlist_count ?? 0);
const cartCount = computed(() => page.props.cart_count ?? 0);
const isHomePage = computed(() => page.url.split('?')[0] === '/');
const customer = computed(() => page.props.auth.customer);
const authModalOpen = ref(Boolean(page.props.customer_auth_modal));
const authView = ref<'login' | 'register'>(
    page.props.customer_auth_modal ?? 'login',
);

const loginForm = useForm({
    login: '',
    password: '',
});

const registerForm = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const openAccountModal = () => {
    authView.value = 'login';
    authModalOpen.value = true;
};

const showLogin = () => {
    registerForm.clearErrors();
    authView.value = 'login';
};

const showRegister = () => {
    loginForm.clearErrors();
    authView.value = 'register';
};

const submitLogin = () => {
    loginForm.post('/customer/login', {
        preserveScroll: true,
        errorBag: 'customerLogin',
        onSuccess: () => {
            loginForm.reset('password');
            authModalOpen.value = false;
        },
        onError: () => {
            authView.value = 'login';
            authModalOpen.value = true;
        },
    });
};

const submitRegister = () => {
    registerForm.post('/customer/register', {
        preserveScroll: true,
        errorBag: 'customerRegister',
        onSuccess: () => {
            registerForm.reset('password', 'password_confirmation');
            authModalOpen.value = false;
        },
        onError: () => {
            authView.value = 'register';
            authModalOpen.value = true;
        },
    });
};

const logoutCustomer = () => {
    router.post('/customer/logout', {}, { preserveScroll: true });
};

watch(
    () => page.props.customer_auth_modal,
    (modal) => {
        if (modal) {
            authView.value = modal;
            authModalOpen.value = true;
        }
    },
);
</script>

<template>
    <div class="bg-[#15512e] text-xs text-white">
        <div
            class="mx-auto flex h-9 w-[min(1180px,calc(100%-32px))] items-center gap-4"
        >
            <div class="flex items-center gap-5">
                <a
                    href="tel:09617551122"
                    class="flex items-center gap-2 hover:text-[#e0f5e5]"
                >
                    <Phone class="h-3.5 w-3.5" />
                    <span>09617 551122</span>
                </a>
                <a
                    href="mailto:support@freshtodaybd.com"
                    class="hidden items-center gap-2 hover:text-[#e0f5e5] sm:flex"
                >
                    <Mail class="h-3.5 w-3.5" />
                    <span>support@freshtodaybd.com</span>
                </a>
            </div>
        </div>
    </div>

    <header class="border-b border-slate-100 bg-white">
        <div
            class="mx-auto flex w-[min(1180px,calc(100%-32px))] items-center gap-4 py-4"
        >
            <Link href="/" class="shrink-0">
                <div class="flex items-center gap-2">
                    <div
                        class="grid h-12 w-12 place-items-center rounded-full bg-[#176536] text-2xl font-black text-white"
                    >
                        F
                    </div>
                    <div class="hidden leading-tight sm:block">
                        <div class="text-xl font-black text-[#15512e]">
                            fresh
                        </div>
                        <div class="-mt-1 text-lg font-bold text-red-500">
                            Today
                        </div>
                    </div>
                </div>
            </Link>

            <div class="relative mx-auto max-w-2xl flex-1">
                <input
                    type="text"
                    placeholder="Search for fish, meat & more..."
                    class="h-12 w-full rounded-md border border-slate-200 bg-white px-4 pr-14 text-sm transition outline-none focus:border-[#319d57] focus:ring-1 focus:ring-[#e0f5e5]"
                />
                <button
                    class="absolute top-0 right-0 grid h-12 w-12 place-items-center rounded-r-md bg-lime-500 text-white hover:bg-lime-600"
                    aria-label="Search"
                >
                    <Search class="h-5 w-5" />
                </button>
            </div>

            <nav class="hidden items-center gap-8 lg:flex">
                <Link
                    href="/wishlist"
                    class="relative text-black hover:text-[#176536]"
                    aria-label="Wishlist"
                >
                    <Heart class="h-7 w-7" />
                    <span
                        class="absolute -top-3 left-5 grid h-5 min-w-5 place-items-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                        >{{ wishlistCount }}</span
                    >
                </Link>
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="flex items-center gap-3 text-xs leading-tight text-black hover:text-[#176536]"
                >
                    <UserRound class="h-6 w-6 shrink-0" />
                    <span>
                        <span class="block font-bold">My Account</span>
                        <span class="block">Dashboard</span>
                    </span>
                </Link>
                <div
                    v-else-if="customer"
                    class="flex items-center gap-3 text-xs leading-tight text-black"
                >
                    <UserRound class="h-6 w-6 shrink-0 text-[#176536]" />
                    <span>
                        <span class="block font-bold">{{ customer.name }}</span>
                        <span class="block">Customer account</span>
                    </span>
                    <button
                        type="button"
                        class="grid h-8 w-8 place-items-center rounded-full border border-slate-200 text-slate-500 hover:border-[#176536] hover:text-[#176536]"
                        aria-label="Logout customer"
                        @click="logoutCustomer"
                    >
                        <LogOut class="h-4 w-4" />
                    </button>
                </div>
                <button
                    v-else
                    type="button"
                    class="flex items-center gap-3 text-left text-xs leading-tight text-black hover:text-[#176536]"
                    @click="openAccountModal"
                >
                    <UserRound class="h-6 w-6 shrink-0" />
                    <span>
                        <span class="block font-bold">My Account</span>
                        <span class="block">Sign in / Register</span>
                    </span>
                </button>
                <Link
                    href="/cart"
                    class="relative flex items-center gap-2 text-sm font-bold text-black hover:text-[#176536]"
                >
                    <span class="relative">
                        <ShoppingCart class="h-7 w-7" />
                        <span
                            class="absolute -top-3 left-5 grid h-5 w-5 place-items-center rounded-full bg-lime-500 text-[10px] font-bold text-white"
                            >{{ cartCount }}</span
                        >
                    </span>
                    <span class="text-base leading-none sm:text-sm xl:text-base"
                        >Cart</span
                    >
                </Link>
            </nav>
        </div>

        <div v-if="isHomePage" class="border-t border-slate-100">
            <div
                class="mx-auto flex w-[min(1180px,calc(100%-32px))] [scrollbar-width:none] gap-8 overflow-x-auto py-3 text-sm [&::-webkit-scrollbar]:hidden"
            >
                <Link
                    v-for="(category, index) in frontendCategories"
                    :key="category.id"
                    href="/shop"
                    class="flex shrink-0 items-center gap-2 pb-2"
                    :class="
                        index === 0
                            ? 'border-b-2 border-[#218a37] font-semibold text-[#218a37]'
                            : 'text-slate-700 hover:text-[#218a37]'
                    "
                >
                    <Fish class="h-4 w-4" />
                    {{ category.name }}
                </Link>
            </div>
        </div>
    </header>

    <Dialog v-model:open="authModalOpen">
        <DialogContent
            class="max-h-[calc(100vh-2rem)] overflow-y-auto sm:max-w-md"
        >
            <DialogHeader>
                <DialogTitle class="text-2xl font-black text-[#15512e]">
                    {{ authView === 'login' ? 'Login' : 'Create Account' }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        authView === 'login'
                            ? 'Login to your Fresh Today customer account.'
                            : 'Register for a Fresh Today customer account.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <div>
                <form
                    v-if="authView === 'login'"
                    class="rounded-md border border-slate-200 p-5"
                    @submit.prevent="submitLogin"
                >
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="customer-login-identifier"
                                >Email or Phone</Label
                            >
                            <Input
                                id="customer-login-identifier"
                                v-model="loginForm.login"
                                type="text"
                                autocomplete="username"
                                placeholder="Enter email or phone"
                                required
                            />
                            <InputError :message="loginForm.errors.login" />
                        </div>
                        <div class="space-y-2">
                            <Label for="customer-login-password"
                                >Password</Label
                            >
                            <Input
                                id="customer-login-password"
                                v-model="loginForm.password"
                                type="password"
                                autocomplete="current-password"
                                required
                            />
                            <InputError :message="loginForm.errors.password" />
                        </div>
                    </div>
                    <Button
                        type="submit"
                        class="mt-5 w-full bg-[#176536] text-white hover:bg-[#15512e]"
                        :disabled="loginForm.processing"
                    >
                        Login
                    </Button>
                    <p class="mt-5 text-center text-sm text-slate-600">
                        New customer?
                        <button
                            type="button"
                            class="font-semibold text-[#176536] hover:underline"
                            @click="showRegister"
                        >
                            Register
                        </button>
                    </p>
                </form>

                <form
                    v-else
                    class="rounded-md border border-slate-200 p-5"
                    @submit.prevent="submitRegister"
                >
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="customer-register-name">Name</Label>
                            <Input
                                id="customer-register-name"
                                v-model="registerForm.name"
                                type="text"
                                autocomplete="name"
                                required
                            />
                            <InputError :message="registerForm.errors.name" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="customer-register-email"
                                    >Email</Label
                                >
                                <Input
                                    id="customer-register-email"
                                    v-model="registerForm.email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                />
                                <InputError
                                    :message="registerForm.errors.email"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="customer-register-phone"
                                    >Phone</Label
                                >
                                <Input
                                    id="customer-register-phone"
                                    v-model="registerForm.phone"
                                    type="tel"
                                    autocomplete="tel"
                                    required
                                />
                                <InputError
                                    :message="registerForm.errors.phone"
                                />
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="customer-register-password"
                                    >Password</Label
                                >
                                <Input
                                    id="customer-register-password"
                                    v-model="registerForm.password"
                                    type="password"
                                    autocomplete="new-password"
                                    required
                                />
                                <InputError
                                    :message="registerForm.errors.password"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    for="customer-register-password-confirmation"
                                    >Confirm</Label
                                >
                                <Input
                                    id="customer-register-password-confirmation"
                                    v-model="registerForm.password_confirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    required
                                />
                            </div>
                        </div>
                    </div>
                    <Button
                        type="submit"
                        class="mt-5 w-full bg-lime-500 text-white hover:bg-lime-600"
                        :disabled="registerForm.processing"
                    >
                        Create Account
                    </Button>
                    <p class="mt-5 text-center text-sm text-slate-600">
                        Already have an account?
                        <button
                            type="button"
                            class="font-semibold text-[#176536] hover:underline"
                            @click="showLogin"
                        >
                            Login
                        </button>
                    </p>
                </form>
            </div>
        </DialogContent>
    </Dialog>
</template>
