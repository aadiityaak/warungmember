<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Pesanan' },
        ] as BreadcrumbItem[],
    },
});

const { orders } = defineProps<{
    orders: Array<{
        id: number;
        user: { name: string };
        status: string;
        total_amount: number;
        notes: string | null;
        created_at: string;
        items: Array<{
            id: number;
            quantity: number;
            price: number;
            subtotal: number;
            product: { name: string; image: string | null };
        }>;
    }>;
}>();

const statusLabels: Record<string, string> = {
    pending: 'Menunggu',
    processing: 'Diproses',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
};

const allStatuses = ['pending', 'processing', 'completed', 'cancelled'];

function statusClasses(status: string, currentStatus: string): string {
    const isActive = status === currentStatus;
    const base = 'inline-flex cursor-pointer items-center border px-2 py-0.5 text-xs font-semibold transition-colors';

    if (isActive) {
        const activeColors: Record<string, string> = {
            pending: 'bg-yellow-50 text-yellow-700 border-yellow-300 z-10',
            processing: 'bg-blue-50 text-blue-700 border-blue-300 z-10',
            completed: 'bg-green-50 text-green-700 border-green-300 z-10',
            cancelled: 'bg-red-50 text-red-500 border-red-200 z-10',
        };
        return `${base} ${activeColors[status] ?? 'bg-[#e5e5e0] text-[#91918c] border-[#dadad3] z-10'}`;
    }

    return `${base} bg-white text-[#91918c] border-[#dadad3] hover:text-[#000000]`;
}

function statusSegmentClass(index: number, total: number): string {
    if (index === 0) return 'rounded-l-full -mr-px';
    if (index === total - 1) return 'rounded-r-full -ml-px';
    return '-mx-px';
}

function updateStatus(orderId: number, status: string) {
    router.put(route('admin.orders.update', orderId), { status }, {
        preserveScroll: true,
        preserveState: true,
    });
}

function productNames(items: Array<{ product: { name: string }; quantity: number }>): string {
    return items.map((i) => `${i.product.name} x${i.quantity}`).join(', ');
}
</script>

<template>
    <Head title="Manajemen Pesanan" />

    <div class="mx-6 pt-6">
        <!-- Header -->
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Manajemen Pesanan
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Kelola pesanan member
            </p>
        </header>

        <!-- Empty -->
        <div v-if="orders.length === 0" class="rounded-2xl bg-[#f6f6f3] py-16 text-center">
            <p class="text-sm leading-[1.4] text-[#62625b]">Belum ada pesanan.</p>
        </div>

        <!-- Table -->
        <div v-else class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#dadad3]">
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] w-12">#</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Member</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden md:table-cell">Produk</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-right text-sm font-bold leading-[1.4] text-[#000000]">Total</th>
                        <th class="px-4 py-3 text-center text-sm font-bold leading-[1.4] text-[#000000] w-32">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="order in orders"
                        :key="order.id"
                        class="border-b border-[#e5e5e0] last:border-0 transition-colors hover:bg-[#fbfbf9]"
                    >
                        <!-- # -->
                        <td class="px-4 py-3">
                            <span class="text-sm leading-[1.4] text-[#91918c]">{{ order.id }}</span>
                        </td>
                        <!-- Member -->
                        <td class="px-4 py-3">
                            <p class="text-sm leading-[1.4] font-semibold text-[#000000]">{{ order.user.name }}</p>
                            <!-- Mobile: show products inline -->
                            <p class="text-xs leading-[1.4] text-[#91918c] md:hidden mt-0.5 truncate max-w-[180px]">{{ productNames(order.items) }}</p>
                        </td>
                        <!-- Produk -->
                        <td class="px-4 py-3 hidden md:table-cell">
                            <p class="text-sm leading-[1.4] text-[#62625b] max-w-xs truncate">{{ productNames(order.items) }}</p>
                            <p v-if="order.notes" class="text-xs leading-[1.4] text-[#91918c] mt-0.5">Catatan: {{ order.notes }}</p>
                        </td>
                        <!-- Tanggal -->
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="text-sm leading-[1.4] text-[#62625b]">
                                {{ new Date(order.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                            </span>
                        </td>
                        <!-- Total -->
                        <td class="px-4 py-3 text-right">
                            <span class="text-sm leading-[1.4] font-semibold text-[#E22625]">Rp{{ order.total_amount.toLocaleString('id-ID') }}</span>
                        </td>
                        <!-- Status -->
                        <td class="px-2 py-3">
                            <div class="inline-flex items-center">
                                <button
                                    v-for="(st, i) in allStatuses"
                                    :key="st"
                                    @click="updateStatus(order.id, st)"
                                    :class="[statusClasses(st, order.status), statusSegmentClass(i, allStatuses.length)]"
                                >
                                    {{ statusLabels[st] }}
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
