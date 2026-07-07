<script setup lang="ts">
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';

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
    <div class="flex flex-col gap-8 mx-6 pt-6">
        <!-- Heading -->
        <header class="space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Dashboard Admin
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Ringkasan data WarungMember
            </p>
        </header>

        <!-- Stat Cards -->
        <div class="grid grid-cols-2 gap-6 lg:grid-cols-4">
            <div class="flex flex-col gap-1 rounded-2xl bg-[#f6f6f3] px-5 py-5">
                <span class="text-sm leading-[1.4] text-[#62625b]">Total Member</span>
                <span class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    {{ stats.total_members }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-2xl bg-[#f6f6f3] px-5 py-4">
                <span class="text-sm leading-[1.4] text-[#62625b]">Poin Beredar</span>
                <span class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    {{ stats.total_points.toLocaleString('id-ID') }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-2xl bg-[#f6f6f3] px-5 py-5">
                <span class="text-sm leading-[1.4] text-[#62625b]">Total Deposit</span>
                <span class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    Rp {{ stats.total_deposit.toLocaleString('id-ID') }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-2xl bg-[#f6f6f3] px-5 py-5">
                <span class="text-sm leading-[1.4] text-[#62625b]">Voucher Redeem</span>
                <span class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    {{ stats.vouchers_redeemed }}
                </span>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="grid gap-8 lg:grid-cols-2">
            <!-- Quick Actions -->
            <div class="rounded-2xl bg-[#f6f6f3] p-8">
                <h3 class="text-lg font-semibold leading-[1.3] text-[#000000]">Aksi Cepat</h3>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a
                        :href="route('admin.members.index')"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#e60023] px-4 py-2.5 text-sm font-bold leading-[1] text-white transition-colors hover:bg-[#cc001f]"
                    >
                        Manajemen Member
                    </a>
                    <a
                        :href="route('admin.rewards.index')"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#e5e5e0] px-4 py-2.5 text-sm font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#c8c8c1]"
                    >
                        Kelola Reward
                    </a>
                    <a
                        :href="route('admin.vouchers.index')"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#e5e5e0] px-4 py-2.5 text-sm font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#c8c8c1]"
                    >
                        Kelola Voucher
                    </a>
                    <a
                        :href="route('admin.deposits.index')"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#e5e5e0] px-4 py-2.5 text-sm font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#c8c8c1]"
                    >
                        Kelola Deposit
                    </a>
                </div>
            </div>

            <!-- Placeholder -->
            <div class="flex items-center justify-center rounded-2xl bg-[#f6f6f3] p-8">
                <p class="text-sm leading-[1.4] text-[#91918c]">Grafik Overview</p>
            </div>
        </div>
    </div>
</template>
