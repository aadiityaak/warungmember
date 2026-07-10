<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';

defineOptions({ layout: MemberLayout });

const { orders } = defineProps<{
    orders: Array<Record<string, any>>;
}>();

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

const paymentLabels: Record<string, string> = {
    cash: 'Tunai',
    qris: 'QRIS',
    transfer: 'Transfer',
    deposit: 'Deposit',
};
</script>

<template>
    <Head title="Riwayat Order" />

    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div>
            <h1 class="text-xl font-bold leading-[1.2] text-[#000000]">Riwayat Order</h1>
            <p class="mt-1 text-sm leading-[1.4] text-[#62625b]">Semua pesanan yang pernah kamu buat</p>
        </div>

        <!-- Empty State -->
        <div v-if="orders.length === 0" class="py-16 text-center">
            <svg class="mx-auto h-16 w-16 text-[#dadad3]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 10a4 4 0 0 1-8 0"/><path d="M3.103 6.034h17.794"/><path d="M3.4 5.467a2 2 0 0 0-.4 1.2V20a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6.667a2 2 0 0 0-.4-1.2l-2-2.667A2 2 0 0 0 17 2H7a2 2 0 0 0-1.6.8z"/></svg>
            <p class="mt-4 text-sm text-[#91918c]">Belum ada riwayat order</p>
            <a
                :href="route('member.products.index')"
                class="mt-3 inline-flex h-9 items-center rounded-full bg-[#E22625] px-5 text-sm font-bold text-white"
            >
                Mulai Belanja
            </a>
        </div>

        <!-- Order List -->
        <div v-if="orders.length" class="flex flex-col gap-3">
            <div
                v-for="order in orders"
                :key="order.id"
                class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white"
            >
                <div class="flex items-center justify-between border-b border-[#dadad3] bg-[#fbfbf9] px-4 py-2.5">
                    <div>
                        <p class="text-xs leading-[1.4] text-[#91918c]">
                            {{ new Date(order.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                            <span v-if="order.outlet" class="ml-2">· {{ order.outlet.name }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span v-if="order.payment_method" class="rounded-full bg-[#f6f6f3] px-2 py-0.5 text-[10px] font-semibold text-[#91918c]">
                            {{ paymentLabels[order.payment_method] ?? order.payment_method }}
                        </span>
                        <span class="text-sm font-bold text-[#E22625]">Rp{{ order.total_amount.toLocaleString('id-ID') }}</span>
                        <span
                            :class="[
                                'inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold',
                                statusColors[order.status] ?? 'bg-[#e5e5e0] text-[#91918c]',
                            ]"
                        >
                            {{ statusLabels[order.status] ?? order.status }}
                        </span>
                    </div>
                </div>
                <div class="p-3">
                    <div v-for="item in order.items" :key="item.id" class="flex justify-between py-1 text-sm">
                        <span class="text-[#000000]">{{ item.product.name }} x{{ item.quantity }}</span>
                        <span class="text-[#62625b]">Rp{{ item.subtotal.toLocaleString('id-ID') }}</span>
                    </div>
                    <div v-if="order.notes" class="mt-2 rounded-lg bg-[#f6f6f3] px-3 py-1.5">
                        <p class="text-xs text-[#91918c]">{{ order.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
