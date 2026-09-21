<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Building2,
    ChartNoAxesCombined,
    ClipboardList,
    LayoutGrid,
    Layers3,
    Package,
    ReceiptText,
    ScrollText,
    ShoppingCart,
    Ruler,
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
import { dashboard } from '@/routes';
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
        items: [
            {
                title: 'Transactions',
                href: '/transactions',
                icon: ReceiptText,
            },
        ],
    },
    {
        title: 'Inventory',
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
        title: 'Report Summery',
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
        items: [
            {
                title: 'Company Setting',
                href: companyEdit(),
                icon: Building2,
            },
            {
                title: 'Profile Setting',
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

        <SidebarContent>
            <NavMain :groups="mainNavGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
