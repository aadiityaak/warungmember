<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import { Package, ShoppingBag, Store, Ticket, Users } from '@lucide/vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
        ] as BreadcrumbItem[],
    },
});

const { stats, recent_orders, chart } = defineProps<{
    stats: {
        total_members: number;
        total_outlets: number;
        total_products: number;
        total_vouchers: number;
        total_points: number;
        total_deposit: number;
        vouchers_redeemed: number;
        total_orders: number;
        completed_orders: number;
        pending_orders: number;
    };
    recent_orders: Array<{
        id: number;
        user_name: string;
        outlet_name: string;
        status: string;
        total_amount: number;
        created_at: string;
    }>;
    chart: {
        labels: string[];
        series: Array<{ name: string; data: number[] }>;
    };
}>();

const statusLabels: Record<string, string> = {
    pending: 'Menunggu',
    processing: 'Diproses',
    completed: 'Selesai',
    cancelled: 'Batal',
};

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700',
    processing: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700',
    cancelled: 'bg-gray-100 text-gray-500',
};

const palette = ['#E22625', '#1a56db', '#069a2d', '#e67700', '#8b5cf6', '#ec4899', '#14b8a6'];

const chartData = computed(() => ({
    labels: chart.labels.map((l: string) => l.slice(5)), // MM-DD
    datasets: chart.series.map((s: { name: string; data: number[] }, i: number) => ({
        label: s.name,
        data: s.data,
        borderColor: palette[i],
        backgroundColor: palette[i] + '20',
        fill: true,
        tension: 0.3,
        pointRadius: 2,
        pointHoverRadius: 4,
    })),
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        intersect: false,
        mode: 'index' as const,
    },
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                usePointStyle: true,
                padding: 20,
                font: { size: 12 },
            },
        },
        tooltip: {
            callbacks: {
                label: (ctx: { dataset: { label: string }; parsed: { y: number } }) =>
                    `${ctx.dataset.label}: ${ctx.parsed.y} pesanan`,
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { stepSize: 1, font: { size: 11 } },
            grid: { color: '#e5e5e0' },
        },
        x: {
            ticks: {
                font: { size: 10 },
                maxTicksLimit: 10,
            },
            grid: { display: false },
        },
    },
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-8 mx-6 pt-6">
        <!-- Heading -->
        <header class="space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Dashboard
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Ringkasan data WarungMember
            </p>
        </header>

        <!-- Top Stat Cards -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            <div class="flex flex-col gap-1 rounded-2xl bg-[#f6f6f3] px-5 py-5">
                <div class="flex items-center gap-2">
                    <Users class="h-4 w-4 text-[#E22625]" />
                    <span class="text-sm leading-[1.4] text-[#62625b]">Member</span>
                </div>
                <span class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    {{ stats.total_members }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-2xl bg-[#f6f6f3] px-5 py-5">
                <div class="flex items-center gap-2">
                    <Store class="h-4 w-4 text-[#E22625]" />
                    <span class="text-sm leading-[1.4] text-[#62625b]">Outlet</span>
                </div>
                <span class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    {{ stats.total_outlets }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-2xl bg-[#f6f6f3] px-5 py-5">
                <div class="flex items-center gap-2">
                    <Package class="h-4 w-4 text-[#E22625]" />
                    <span class="text-sm leading-[1.4] text-[#62625b]">Produk</span>
                </div>
                <span class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    {{ stats.total_products }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-2xl bg-[#f6f6f3] px-5 py-5">
                <div class="flex items-center gap-2">
                    <Ticket class="h-4 w-4 text-[#E22625]" />
                    <span class="text-sm leading-[1.4] text-[#62625b]">Voucher</span>
                </div>
                <span class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    {{ stats.total_vouchers }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-2xl bg-[#f6f6f3] px-5 py-5">
                <div class="flex items-center gap-2">
                    <ShoppingBag class="h-4 w-4 text-[#E22625]" />
                    <span class="text-sm leading-[1.4] text-[#62625b]">Pesanan</span>
                </div>
                <span class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    {{ stats.total_orders }}
                </span>
            </div>
        </div>

        <!-- Second Row: Financial + Order Status -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex flex-col gap-1 rounded-2xl bg-white border border-[#dadad3] px-5 py-5">
                <span class="text-sm leading-[1.4] text-[#62625b]">Poin Beredar</span>
                <span class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    {{ stats.total_points.toLocaleString('id-ID') }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-2xl bg-white border border-[#dadad3] px-5 py-5">
                <span class="text-sm leading-[1.4] text-[#62625b]">Total Deposit</span>
                <span class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    Rp {{ stats.total_deposit.toLocaleString('id-ID') }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-2xl bg-white border border-[#dadad3] px-5 py-5">
                <span class="text-sm leading-[1.4] text-[#62625b]">Pesanan Selesai</span>
                <span class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-green-600">
                    {{ stats.completed_orders }}
                </span>
            </div>

            <div class="flex flex-col gap-1 rounded-2xl bg-white border border-[#dadad3] px-5 py-5">
                <span class="text-sm leading-[1.4] text-[#62625b]">Pesanan Menunggu</span>
                <span class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-yellow-600">
                    {{ stats.pending_orders }}
                </span>
            </div>
        </div>

        <!-- Order Trend Chart -->
        <div v-if="chart.series.length" class="rounded-2xl border border-[#dadad3] bg-white overflow-hidden">
            <div class="px-6 py-4 border-b border-[#dadad3]">
                <h3 class="text-sm font-bold leading-[1.4] text-[#000000]">Tren Pesanan — 30 Hari Terakhir</h3>
            </div>
            <div class="p-6">
                <div style="height: 300px;">
                    <Line :data="chartData" :options="chartOptions" />
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Recent Orders -->
            <div class="lg:col-span-2 rounded-2xl border border-[#dadad3] bg-white overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-[#dadad3]">
                    <h3 class="text-sm font-bold leading-[1.4] text-[#000000]">Pesanan Terbaru</h3>
                    <Link
                        :href="route('admin.orders.index')"
                        class="text-xs font-semibold text-[#E22625] hover:underline"
                    >
                        Lihat Semua
                    </Link>
                </div>
                <table v-if="recent_orders.length" class="w-full">
                    <thead>
                        <tr class="border-b border-[#dadad3]">
                            <th class="px-6 py-2.5 text-left text-xs font-bold leading-[1.4] text-[#91918c]">#</th>
                            <th class="px-6 py-2.5 text-left text-xs font-bold leading-[1.4] text-[#91918c]">Member</th>
                            <th class="px-6 py-2.5 text-left text-xs font-bold leading-[1.4] text-[#91918c] hidden sm:table-cell">Outlet</th>
                            <th class="px-6 py-2.5 text-left text-xs font-bold leading-[1.4] text-[#91918c]">Total</th>
                            <th class="px-6 py-2.5 text-left text-xs font-bold leading-[1.4] text-[#91918c]">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in recent_orders"
                            :key="order.id"
                            class="border-b border-[#e5e5e0] last:border-0 transition-colors hover:bg-[#fbfbf9]"
                        >
                            <td class="px-6 py-3">
                                <span class="text-sm leading-[1.4] font-mono text-[#91918c]">#{{ order.id }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-sm leading-[1.4] font-semibold text-[#000000]">{{ order.user_name }}</span>
                            </td>
                            <td class="px-6 py-3 hidden sm:table-cell">
                                <span class="text-sm leading-[1.4] text-[#62625b]">{{ order.outlet_name }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-sm leading-[1.4] font-semibold text-[#000000]">Rp {{ order.total_amount.toLocaleString('id-ID') }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <span
                                    :class="[
                                        'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold leading-[1.4]',
                                        statusColors[order.status],
                                    ]"
                                >
                                    {{ statusLabels[order.status] ?? order.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-else class="px-6 py-12 text-center">
                    <p class="text-sm leading-[1.4] text-[#91918c]">Belum ada pesanan</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-2xl border border-[#dadad3] bg-white p-6">
                <h3 class="text-sm font-bold leading-[1.4] text-[#000000] mb-4">Aksi Cepat</h3>
                <div class="flex flex-col gap-2">
                    <Link
                        :href="route('admin.members.index')"
                        class="inline-flex items-center justify-center rounded-full bg-[#E22625] px-4 py-2.5 text-sm font-bold leading-[1] text-white transition-colors hover:bg-[#cc001f]"
                    >
                        Manajemen Member
                    </Link>
                    <Link
                        :href="route('admin.orders.index')"
                        class="inline-flex items-center justify-center rounded-full border border-[#dadad3] px-4 py-2.5 text-sm font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#f6f6f3]"
                    >
                        Kelola Pesanan
                    </Link>
                    <Link
                        :href="route('admin.rewards.index')"
                        class="inline-flex items-center justify-center rounded-full border border-[#dadad3] px-4 py-2.5 text-sm font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#f6f6f3]"
                    >
                        Kelola Reward
                    </Link>
                    <Link
                        :href="route('admin.vouchers.index')"
                        class="inline-flex items-center justify-center rounded-full border border-[#dadad3] px-4 py-2.5 text-sm font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#f6f6f3]"
                    >
                        Kelola Voucher
                    </Link>
                    <Link
                        :href="route('admin.deposits.index')"
                        class="inline-flex items-center justify-center rounded-full border border-[#dadad3] px-4 py-2.5 text-sm font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#f6f6f3]"
                    >
                        Kelola Deposit
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
