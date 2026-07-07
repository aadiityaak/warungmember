<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CreditCard, Gift, LayoutGrid, Package, Settings, ShoppingBag, Store, Ticket, Users } from '@lucide/vue';
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
import { dashboard } from '@/routes/admin';
import { index as membersIndex } from '@/routes/admin/members';
import { index as rewardsIndex } from '@/routes/admin/rewards';
import { index as depositsIndex } from '@/routes/admin/deposits';
import { index as vouchersIndex } from '@/routes/admin/vouchers';
import { index as productsIndex } from '@/routes/admin/products';
import { index as outletsIndex } from '@/routes/admin/outlets';
import { index as ordersIndex } from '@/routes/admin/orders';
import { edit as editProfile } from '@/routes/profile';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Member',
        href: membersIndex(),
        icon: Users,
    },
    {
        title: 'Outlet',
        href: outletsIndex(),
        icon: Store,
    },
    {
        title: 'Produk',
        href: productsIndex(),
        icon: Package,
    },
    {
        title: 'Reward',
        href: rewardsIndex(),
        icon: Gift,
    },
    {
        title: 'Deposit',
        href: depositsIndex(),
        icon: CreditCard,
    },
    {
        title: 'Voucher',
        href: vouchersIndex(),
        icon: Ticket,
    },
    {
        title: 'Pesanan',
        href: ordersIndex(),
        icon: ShoppingBag,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Pengaturan',
        href: editProfile(),
        icon: Settings,
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
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
