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
    return 'Rp' + n.toLocaleString('id-ID');
}

function padRight(s: string, len: number): string {
    return s + ' '.repeat(Math.max(0, len - s.length));
}

const paymentLabels: Record<string, string> = {
    cash: 'Tunai',
    qris: 'QRIS',
    transfer: 'Transfer',
};

onMounted(() => {
    setTimeout(() => window.print(), 300);
});
</script>

<template>
    <Head title="Struk Pesanan" />

    <div class="receipt-print">
        <div class="header">
            <div class="store-name">{{ order.outlet?.name ?? 'Warung Member' }}</div>
            <div class="divider-dash">- - - - - - - - - - - - - - - -</div>
        </div>

        <div class="body">
            <div class="info-row">
                <span>No</span>
                <span>#{{ order.id }}</span>
            </div>
            <div class="info-row">
                <span>Member</span>
                <span>{{ order.user.name }}</span>
            </div>
            <div class="info-row">
                <span>Tgl</span>
                <span>{{ new Date(order.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</span>
            </div>
            <div class="info-row">
                <span>Bayar</span>
                <span>{{ paymentLabels[order.payment_method] ?? order.payment_method }}</span>
            </div>

            <div class="divider-dash">- - - - - - - - - - - - - - - -</div>

            <div class="items">
                <div v-for="item in order.items" :key="item.id" class="item">
                    <div class="item-name-qty">
                        <span class="name">{{ item.product.name }}</span>
                        <span class="qty">x{{ item.quantity }}</span>
                    </div>
                    <div class="item-subtotal">{{ formatRupiah(item.subtotal) }}</div>
                </div>
            </div>

            <div class="divider-dash">- - - - - - - - - - - - - - - -</div>

            <div class="total-row">
                <span class="label">TOTAL</span>
                <span class="value">{{ formatRupiah(order.total_amount) }}</span>
            </div>

            <div v-if="order.payment_method === 'cash' && order.paid_amount !== null" class="payment-section">
                <div class="info-row">
                    <span>Tunai</span>
                    <span>{{ formatRupiah(order.paid_amount) }}</span>
                </div>
                <div v-if="order.change !== null" class="info-row change-row">
                    <span>Kembali</span>
                    <span>{{ formatRupiah(order.change) }}</span>
                </div>
            </div>

            <div class="divider-dash">- - - - - - - - - - - - - - - -</div>

            <div class="footer-text">Terima kasih</div>
            <div class="footer-text small">Selamat berbelanja kembali</div>
        </div>
    </div>
</template>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body {
    background: #fff;
}

body {
    font-family: 'Courier New', Courier, 'Lucida Console', monospace;
    display: flex;
    justify-content: center;
    min-height: 100vh;
}

.receipt-print {
    width: 80mm;
    padding: 4mm 3mm;
    font-size: 10px;
    line-height: 1.4;
    color: #000;
    text-transform: uppercase;
}

.header {
    text-align: center;
    margin-bottom: 4px;
}

.store-name {
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 1px;
    margin-bottom: 2px;
}

.divider-dash {
    text-align: center;
    letter-spacing: 2px;
    color: #555;
    font-size: 9px;
    line-height: 1.8;
}

.body {
    width: 100%;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 1px 0;
    font-size: 9px;
}

.items {
    width: 100%;
}

.item {
    display: flex;
    justify-content: space-between;
    padding: 1px 0;
    font-size: 9px;
}

.item-name-qty {
    display: flex;
    gap: 4px;
}

.item .name {
    max-width: 48mm;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.item .qty {
    color: #555;
}

.item-subtotal {
    white-space: nowrap;
}

.total-row {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    font-weight: bold;
    padding: 2px 0;
}

.total-row .label {
    letter-spacing: 2px;
}

.payment-section {
    margin-top: 2px;
}

.change-row {
    font-weight: bold;
}

.footer-text {
    text-align: center;
    font-size: 9px;
    letter-spacing: 1px;
    padding: 1px 0;
}

.footer-text.small {
    font-size: 8px;
    color: #555;
}

@media print {
    html, body {
        background: #fff;
        margin: 0;
        padding: 0;
    }
    .receipt-print {
        padding: 2mm 3mm;
    }
    @page {
        margin: 0;
        size: 80mm auto;
    }
}

@media screen {
    .receipt-print {
        border: 1px dashed #ccc;
        margin: 16px auto;
    }
}
</style>
