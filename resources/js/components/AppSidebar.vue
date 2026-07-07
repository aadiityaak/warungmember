<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CreditCard, Gift, LayoutGrid, Settings, Ticket, Users } from '@lucide/vue';
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
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('admin.dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'Member',
        href: route('admin.members.index'),
        icon: Users,
    },
    {
        title: 'Reward',
        href: route('admin.rewards.index'),
        icon: Gift,
    },
    {
        title: 'Deposit',
        href: route('admin.deposits.index'),
        icon: CreditCard,
    },
    {
        title: 'Voucher',
        href: route('admin.vouchers.index'),
        icon: Ticket,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Pengaturan',
        href: route('profile.edit'),
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
