<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const { order } = defineProps<{
    order: {
        id: number;
        user: { name: string };
        status: string;
        total_amount: number;
        paid_amount: number | null;
        change: number | null;
        payment_method: string;
        notes: string | null;
        created_at: string;
        outlet: { id: number; name: string } | null;
        items: Array<{
            id: number;
            quantity: number;
            price: number;
            subtotal: number;
            product: { id: number; name: string };
        }>;
    };
}>();

function formatRupiah(n: number): string {
    return 'Rp ' + n.toLocaleString('id-ID');
}

const paymentLabels: Record<string, string> = {
    cash: 'Tunai',
    qris: 'QRIS',
    transfer: 'Transfer',
};

onMounted(() => {
    window.print();
});
</script>

<template>
    <Head title="Struk Pesanan" />

    <div class="receipt">
        <div class="receipt-content">
            <!-- Header -->
            <div class="text-center border-b border-dashed pb-3 mb-3">
                <h1 class="text-lg font-bold">{{ order.outlet?.name ?? 'Warung Member' }}</h1>
                <p class="text-xs text-gray-500">Struk Pesanan</p>
            </div>

            <!-- Info -->
            <div class="text-xs space-y-1 mb-3">
                <div class="flex justify-between">
                    <span>No. Pesanan</span>
                    <span class="font-bold">#{{ order.id }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Member</span>
                    <span>{{ order.user.name }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Tanggal</span>
                    <span>{{ new Date(order.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Pembayaran</span>
                    <span>{{ paymentLabels[order.payment_method] ?? order.payment_method }}</span>
                </div>
            </div>

            <!-- Items -->
            <div class="border-t border-dashed pt-3 mb-3">
                <div class="text-xs font-bold mb-1">Pesanan:</div>
                <div v-for="item in order.items" :key="item.id" class="text-xs flex justify-between py-1">
                    <div class="flex-1">
                        <span>{{ item.product.name }}</span>
                        <span class="text-gray-500"> x{{ item.quantity }}</span>
                    </div>
                    <span class="ml-2">{{ formatRupiah(item.subtotal) }}</span>
                </div>
            </div>

            <!-- Total -->
            <div class="border-t border-dashed pt-2 space-y-1 text-xs font-bold">
                <div class="flex justify-between">
                    <span>Total</span>
                    <span>{{ formatRupiah(order.total_amount) }}</span>
                </div>
                <div v-if="order.payment_method === 'cash' && order.paid_amount !== null" class="font-normal text-gray-600">
                    <div class="flex justify-between">
                        <span>Tunai</span>
                        <span>{{ formatRupiah(order.paid_amount) }}</span>
                    </div>
                    <div v-if="order.change !== null" class="flex justify-between">
                        <span>Kembalian</span>
                        <span>{{ formatRupiah(order.change) }}</span>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="text-center border-t border-dashed pt-3 mt-3">
                <p class="text-xs text-gray-500">Terima kasih telah berbelanja</p>
            </div>
        </div>
    </div>
</template>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: white;
    font-family: 'Courier New', Courier, monospace;
}

.receipt {
    max-width: 80mm;
    margin: 0 auto;
    padding: 8px;
    font-size: 12px;
    line-height: 1.4;
    color: #000;
}

.receipt-content {
    width: 100%;
}

@media print {
    body {
        margin: 0;
        padding: 0;
    }
    .receipt {
        max-width: 100%;
        padding: 4px 8px;
    }
    @page {
        margin: 2mm;
        size: 80mm auto;
    }
}
</style>
