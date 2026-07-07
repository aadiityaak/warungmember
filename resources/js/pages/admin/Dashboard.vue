<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import Heading from '@/components/Heading.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
        ] as BreadcrumbItem[],
    },
});

const { stats } = defineProps<{
    stats: {
        total_members: number;
        total_points: number;
        total_deposit: number;
        vouchers_redeemed: number;
    };
}>();
</script>

<template>
    <Heading title="Dashboard Admin" description="Ringkasan data WarungMember" />

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <Card>
            <CardHeader class="flex flex-row items-center justify-between pb-2">
                <CardTitle class="text-sm font-medium text-muted-foreground">Total Member</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold">{{ stats.total_members }}</div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader class="flex flex-row items-center justify-between pb-2">
                <CardTitle class="text-sm font-medium text-muted-foreground">Poin Beredar</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold">{{ stats.total_points }}</div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader class="flex flex-row items-center justify-between pb-2">
                <CardTitle class="text-sm font-medium text-muted-foreground">Total Deposit</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold">Rp {{ stats.total_deposit.toLocaleString('id-ID') }}</div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader class="flex flex-row items-center justify-between pb-2">
                <CardTitle class="text-sm font-medium text-muted-foreground">Voucher Redeem</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold">{{ stats.vouchers_redeemed }}</div>
            </CardContent>
        </Card>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <Card>
            <CardHeader>
                <CardTitle>Aksi Cepat</CardTitle>
            </CardHeader>
            <CardContent class="flex flex-wrap gap-3">
                <Button as="child">
                    <Link :href="route('admin.members.index')">Manajemen Member</Link>
                </Button>
                <Button variant="outline" as="child">
                    <Link :href="route('admin.members.index')">Kelola Reward</Link>
                </Button>
                <Button variant="outline" as="child">
                    <Link :href="route('admin.members.index')">Kelola Voucher</Link>
                </Button>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Grafik Overview</CardTitle>
            </CardHeader>
            <CardContent class="flex items-center justify-center p-6">
                <PlaceholderPattern />
            </CardContent>
        </Card>
    </div>
</template>
