<script setup lang="ts">
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    Fish,
    Heart,
    Mail,
    Phone,
    Search,
    ShoppingCart,
    UserRound,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useCurrency } from '@/composables/useCurrency';
import { dashboard } from '@/routes';

type FrontendCategory = {
    id: number;
    name: string;
    icon_url: string | null;
};

type SearchProduct = {
    id: number;
    category_name: string | null;
    name: string;
    slug: string;
    thumbnail_url: string | null;
    regular_price: string | null;
    sale_price: string | null;
};

type FrontendCustomer = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    avatar: string | null;
};

type FrontendUser = {
    name: string;
};

type SharedSettings = {
    company_name: string | null;
    email: string | null;
    phone: string | null;
    logo_url: string | null;
};

const { money } = useCurrency();

const page = usePage<{
    settings?: SharedSettings;
    frontend_categories?: FrontendCategory[];
    frontend_search_products?: SearchProduct[];
    customer_auth_modal?: 'login' | 'register' | null;
    wishlist_count?: number;
    cart_count?: number;
    auth: {
        user: FrontendUser | null;
        customer: FrontendCustomer | null;
    };
}>();

const frontendCategories = computed(() => page.props.frontend_categories ?? []);
const searchProducts = computed(
    () => page.props.frontend_search_products ?? [],
);
const wishlistCount = computed(() => page.props.wishlist_count ?? 0);
const cartCount = computed(() => page.props.cart_count ?? 0);
const isHomePage = computed(() => page.url.split('?')[0] === '/');
const customer = computed(() => page.props.auth.customer);
const settingsPhone = computed(() => page.props.settings?.phone?.trim() || '');
const settingsEmail = computed(() => page.props.settings?.email?.trim() || '');
const settingsLogoUrl = computed(() => page.props.settings?.logo_url || '');
const settingsCompanyName = computed(
    () => page.props.settings?.company_name?.trim() || 'Fresh Today',
);
const settingsLogoAlt = computed(() => settingsCompanyName.value + ' logo');
const phoneHref = computed(() => {
    const firstPhone = settingsPhone.value.split(/[,\n]/)[0]?.trim() ?? '';
    const normalizedPhone = firstPhone.replace(/[^\d+]/g, '');

    return normalizedPhone ? 'tel:' + normalizedPhone : '';
});
const authModalOpen = ref(Boolean(page.props.customer_auth_modal));
const logoutConfirmationOpen = ref(false);
const searchQuery = ref('');
const searchFocused = ref(false);
const authView = ref<'login' | 'register'>(
    page.props.customer_auth_modal ?? 'login',
);
const normalizedSearchQuery = computed(() =>
    searchQuery.value.trim().toLowerCase(),
);
const searchSuggestions = computed(() => {
    if (!normalizedSearchQuery.value) {
        return [];
    }

    return searchProducts.value
        .filter((product) => {
            const searchableText = [product.name, product.category_name ?? '']
                .join(' ')
                .toLowerCase();

            return searchableText.includes(normalizedSearchQuery.value);
        })
        .slice(0, 6);
});
const searchSuggestionsOpen = computed(
    () => searchFocused.value && searchQuery.value.trim().length > 0,
);

const productUrl = (product: SearchProduct) => `/product/${product.slug}`;
const productPrice = (product: SearchProduct) => {
    const price = product.sale_price || product.regular_price;

    return price ? money(price) : '';
};
const fallbackProductImage = (name: string) =>
    `https://placehold.co/96x96/f5f7f4/23833f?text=${encodeURIComponent(name)}`;
const closeSearchSuggestions = () => {
    window.setTimeout(() => {
        searchFocused.value = false;
    }, 120);
};
const submitSearch = () => {
    const product = searchSuggestions.value[0];

    if (product) {
        router.visit(productUrl(product));

        return;
    }

    if (searchQuery.value.trim()) {
        router.visit('/shop');
    }
};

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

const logoutForm = useForm({});

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

const confirmLogout = () => {
    logoutForm.post('/customer/logout', {
        preserveScroll: true,
        onSuccess: () => {
            logoutConfirmationOpen.value = false;
        },
    });
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
            class="mx-auto flex h-9 w-[min(1180px,calc(100%-32px))] items-center justify-between gap-4"
        >
            <div class="flex items-center gap-5">
                <a
                    v-if="settingsPhone"
                    :href="phoneHref || undefined"
                    class="flex items-center gap-2 hover:text-[#e0f5e5]"
                >
                    <Phone class="h-3.5 w-3.5" />
                    <span>{{ settingsPhone }}</span>
                </a>
                <a
                    v-if="settingsEmail"
                    :href="'mailto:' + settingsEmail"
                    class="hidden items-center gap-2 hover:text-[#e0f5e5] sm:flex"
                >
                    <Mail class="h-3.5 w-3.5" />
                    <span>{{ settingsEmail }}</span>
                </a>
            </div>

            <div class="hidden items-center gap-5 md:flex">
                <Link href="/wishlist" class="hover:text-[#e0f5e5]">
                    Wishlist ({{ wishlistCount }})
                </Link>
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="max-w-40 truncate font-semibold hover:text-[#e0f5e5]"
                >
                    {{ $page.props.auth.user.name }}
                </Link>
                <button
                    v-else-if="customer"
                    type="button"
                    class="max-w-40 truncate font-semibold hover:text-[#e0f5e5]"
                    @click="logoutConfirmationOpen = true"
                >
                    {{ customer.name }}
                </button>
                <button
                    v-else
                    type="button"
                    class="font-semibold hover:text-[#e0f5e5]"
                    @click="openAccountModal"
                >
                    Sign In / Register
                </button>
            </div>
        </div>
    </div>

    <header class="border-b border-slate-100 bg-white">
        <div
            class="mx-auto flex w-[min(1180px,calc(100%-32px))] items-center gap-4 py-4"
        >
            <Link href="/" class="shrink-0">
                <img
                    v-if="settingsLogoUrl"
                    :src="settingsLogoUrl"
                    :alt="settingsLogoAlt"
                    class="h-14 w-auto max-w-40 object-contain"
                />
                <div v-else class="flex items-center gap-2">
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

            <form
                class="relative mx-auto max-w-2xl flex-1"
                @submit.prevent="submitSearch"
            >
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search for fish, meat & more..."
                    class="h-12 w-full rounded-md border border-slate-200 bg-white px-4 pr-14 text-sm transition outline-none focus:border-[#319d57] focus:ring-1 focus:ring-[#e0f5e5]"
                    autocomplete="off"
                    @focus="searchFocused = true"
                    @blur="closeSearchSuggestions"
                    @keydown.escape="searchFocused = false"
                />
                <button
                    type="submit"
                    class="absolute top-0 right-0 grid h-12 w-12 place-items-center rounded-r-md bg-lime-500 text-white hover:bg-lime-600"
                    aria-label="Search"
                >
                    <Search class="h-5 w-5" />
                </button>

                <div
                    v-if="searchSuggestionsOpen"
                    class="absolute top-[calc(100%+8px)] right-0 left-0 z-50 overflow-hidden rounded-md border border-slate-200 bg-white shadow-xl"
                >
                    <div v-if="searchSuggestions.length" class="py-2">
                        <Link
                            v-for="product in searchSuggestions"
                            :key="product.id"
                            :href="productUrl(product)"
                            class="flex items-center gap-3 px-3 py-2.5 hover:bg-[#f1f8f2]"
                            @mousedown.prevent
                            @click="searchQuery = product.name"
                        >
                            <img
                                :src="
                                    product.thumbnail_url ??
                                    fallbackProductImage(product.name)
                                "
                                :alt="product.name"
                                class="h-12 w-12 rounded object-cover"
                            />
                            <span class="min-w-0 flex-1">
                                <span
                                    class="block truncate text-sm font-semibold text-slate-800"
                                    >{{ product.name }}</span
                                >
                                <span
                                    class="block truncate text-xs text-slate-500"
                                    >{{
                                        product.category_name || 'Fresh Today'
                                    }}</span
                                >
                            </span>
                            <span
                                v-if="productPrice(product)"
                                class="shrink-0 text-sm font-bold text-[#176536]"
                                >{{ productPrice(product) }}</span
                            >
                        </Link>
                    </div>
                    <div v-else class="px-4 py-3 text-sm text-slate-500">
                        No products found
                    </div>
                </div>
            </form>

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
                    class="flex items-center gap-1 text-xs leading-tight text-black"
                >
                    <UserRound class="h-6 w-6 shrink-0 text-[#176536]" />
                    <span>
                        <span class="block font-bold">{{ customer.name }}</span>
                        <span class="block">Customer account</span>
                    </span>
                </div>
                <button
                    v-else
                    type="button"
                    class="flex items-center gap-1 text-left text-xs leading-tight text-black hover:text-[#176536]"
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

    <Dialog v-model:open="logoutConfirmationOpen">
        <DialogContent class="sm:max-w-sm">
            <DialogHeader>
                <DialogTitle>Logout</DialogTitle>
                <DialogDescription>
                    Are you sure you want to logout?
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button
                    type="button"
                    variant="outline"
                    :disabled="logoutForm.processing"
                    @click="logoutConfirmationOpen = false"
                >
                    No
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    :disabled="logoutForm.processing"
                    @click="confirmLogout"
                >
                    {{ logoutForm.processing ? 'Logging out...' : 'Yes' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
