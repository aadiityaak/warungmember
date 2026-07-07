<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
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

const statusForm = useForm({});

const statusLabels: Record<string, string> = {
    pending: 'Menunggu',
    processing: 'Diproses',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
};

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700',
    processing: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700',
    cancelled: 'bg-[#e5e5e0] text-[#91918c]',
};

function updateStatus(orderId: number, status: string) {
    statusForm.put(route('admin.orders.update', orderId), {
        data: { status },
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Manajemen Pesanan" />

    <div class="mx-6 pt-6">
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Manajemen Pesanan
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Kelola pesanan member
            </p>
        </header>

        <div v-if="orders.length === 0" class="rounded-2xl bg-[#f6f6f3] py-16 text-center">
            <p class="text-sm leading-[1.4] text-[#62625b]">Belum ada pesanan.</p>
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="order in orders"
                :key="order.id"
                class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white"
            >
                <!-- Order Header -->
                <div class="flex items-center justify-between border-b border-[#dadad3] bg-[#fbfbf9] px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold leading-[1.4] text-[#000000]">{{ order.user.name }}</p>
                        <p class="text-xs leading-[1.4] text-[#91918c]">
                            {{ new Date(order.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold leading-[1.4] text-[#E22625]">Rp{{ order.total_amount.toLocaleString('id-ID') }}</span>
                        <select
                            :value="order.status"
                            @change="updateStatus(order.id, ($event.target as HTMLSelectElement).value)"
                            :disabled="statusForm.processing"
                            class="rounded-full border border-[#dadad3] px-2.5 py-1 text-xs font-semibold leading-[1.4] text-[#000000] focus:outline-none focus:ring-2 focus:ring-[#E22625]"
                        >
                            <option value="pending">Menunggu</option>
                            <option value="processing">Diproses</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="p-4">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-[#e5e5e0]">
                                <th class="pb-2 text-left text-xs font-bold leading-[1.4] text-[#91918c]">Produk</th>
                                <th class="pb-2 text-center text-xs font-bold leading-[1.4] text-[#91918c] w-16">Qty</th>
                                <th class="pb-2 text-right text-xs font-bold leading-[1.4] text-[#91918c] w-24">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in order.items"
                                :key="item.id"
                                class="border-b border-[#f6f6f3] last:border-0"
                            >
                                <td class="py-2 text-sm leading-[1.4] text-[#000000]">{{ item.product.name }}</td>
                                <td class="py-2 text-center text-sm leading-[1.4] text-[#62625b]">{{ item.quantity }}</td>
                                <td class="py-2 text-right text-sm leading-[1.4] font-semibold text-[#000000]">Rp{{ item.subtotal.toLocaleString('id-ID') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Notes -->
                    <div v-if="order.notes" class="mt-3 rounded-xl bg-[#f6f6f3] px-3 py-2">
                        <p class="text-xs text-[#91918c]">Catatan: {{ order.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
