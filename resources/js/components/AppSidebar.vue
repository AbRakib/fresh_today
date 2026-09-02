<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Building2,
    LayoutGrid,
    Layers3,
    Package,
    Palette,
    ShieldCheck,
    ShoppingCart,
    Tags,
    UserCog,
    Users,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
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
import { edit as appearanceEdit } from '@/routes/appearance';
import { edit as companyEdit } from '@/routes/company';
import { edit as profileEdit } from '@/routes/profile';
import { edit as securityEdit } from '@/routes/security';
import type { NavGroup, NavItem } from '@/types';

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
            {
                title: 'Security Setting',
                href: securityEdit(),
                icon: ShieldCheck,
            },
            {
                title: 'Appearance Setting',
                href: appearanceEdit(),
                icon: Palette,
            },
        ],
    },
];

const footerNavItems: NavItem[] = [];
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
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
