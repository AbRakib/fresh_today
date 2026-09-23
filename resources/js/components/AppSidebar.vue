<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Building2,
    ChartNoAxesCombined,
    ClipboardList,
    ExternalLink,
    Landmark,
    LayoutGrid,
    Layers3,
    Package,
    ReceiptText,
    ScrollText,
    ShoppingCart,
    Ruler,
    Settings,
    Tags,
    Truck,
    UserCog,
    Users,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, home } from '@/routes';
import { edit as companyEdit } from '@/routes/company';
import { edit as profileEdit } from '@/routes/profile';
import type { NavGroup } from '@/types';

const mainNavGroups: NavGroup[] = [
    {
        title: 'Main',
        items: [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutGrid,
            },
        ],
    },
    {
        title: 'Sale',
        icon: ShoppingCart,
        collapsible: true,
        items: [
            {
                title: 'Orders',
                href: '/orders',
                icon: ShoppingCart,
            },
            {
                title: 'Customers',
                href: '/customers',
                icon: Users,
            },
        ],
    },
    {
        title: 'Purchase',
        icon: ClipboardList,
        collapsible: true,
        items: [
            {
                title: 'Purchase Items',
                href: '/purchases',
                icon: ClipboardList,
            },
            {
                title: 'Suppliers',
                href: '/suppliers',
                icon: Truck,
            },
        ],
    },
    {
        title: 'Accounting',
        icon: Landmark,
        collapsible: true,
        items: [
            {
                title: 'Transactions',
                href: '/transactions',
                icon: ReceiptText,
            },
            {
                title: 'Bank Accounts',
                href: '/bank-accounts',
                icon: Landmark,
            },
        ],
    },
    {
        title: 'Inventory',
        icon: Package,
        collapsible: true,
        items: [
            {
                title: 'Products',
                href: '/products',
                icon: Package,
            },
            {
                title: 'Categories',
                href: '/categories',
                icon: Tags,
            },
            {
                title: 'Subcategories',
                href: '/subcategories',
                icon: Layers3,
            },
            {
                title: 'Units',
                href: '/units',
                icon: Ruler,
            },
        ],
    },
    {
        title: 'Report',
        icon: ChartNoAxesCombined,
        collapsible: true,
        items: [
            {
                title: 'Stock Report',
                href: '/reports/stock',
                icon: ScrollText,
            },
            {
                title: 'Profit Loss Report',
                href: '/reports/profit-loss',
                icon: ChartNoAxesCombined,
            },
        ],
    },
    {
        title: 'Settings',
        label: 'Administrator',
        icon: Settings,
        collapsible: true,
        items: [
            {
                title: 'Company Settings',
                href: companyEdit(),
                icon: Building2,
            },
            {
                title: 'Profile Settings',
                href: profileEdit(),
                icon: UserCog,
            },
        ],
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent
            class="[scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
        >
            <NavMain :groups="mainNavGroups" />
        </SidebarContent>

        <SidebarFooter>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton as-child tooltip="Visit Website">
                        <a
                            :href="home().url"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <ExternalLink />
                            <span>Visit Website</span>
                        </a>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
