<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';

defineOptions({ layout: MemberLayout });

const { orders } = defineProps<{
    orders: Array<Record<string, any>>;
}>();

const statusConfig: Record<string, { label: string; dot: string; bg: string }> = {
    pending: { label: 'Menunggu', dot: 'bg-yellow-500', bg: 'bg-yellow-50' },
    processing: { label: 'Diproses', dot: 'bg-blue-500', bg: 'bg-blue-50' },
    completed: { label: 'Selesai', dot: 'bg-green-500', bg: 'bg-green-50' },
    cancelled: { label: 'Dibatalkan', dot: 'bg-[#91918c]', bg: 'bg-[#f6f6f3]' },
};

const paymentLabels: Record<string, string> = {
    cash: 'Tunai',
    qris: 'QRIS',
    transfer: 'Transfer',
    deposit: 'Deposit',
};

function formatRupiah(n: number): string {
    return 'Rp' + n.toLocaleString('id-ID');
}

function formatDate(dateStr: string): { date: string; time: string } {
    const d = new Date(dateStr);
    return {
        date: d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }),
        time: d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
    };
}

const groupedOrders = computed(() => {
    const groups: Record<string, typeof orders> = {};
    for (const order of orders) {
        const d = new Date(order.created_at);
        const key = d.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
        if (!groups[key]) groups[key] = [];
        groups[key].push(order);
    }
    return groups;
});

const showReceipt = ref(new Set<number>());

function toggleReceipt(orderId: number) {
    const s = new Set(showReceipt.value);
    if (s.has(orderId)) {
        s.delete(orderId);
    } else {
        s.add(orderId);
    }
    showReceipt.value = s;
}

function receiptFormatRupiah(n: number | null | undefined): string {
    if (n == null) return 'Rp0';
    return 'Rp' + n.toLocaleString('id-ID');
}
</script>

<template>
    <Head title="Riwayat Order" />

    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-[22px] font-bold leading-[1.2] tracking-tight text-[#000000]">
                    Riwayat Order
                </h1>
                <p class="mt-1 text-sm leading-[1.4] text-[#62625b]">
                    {{ orders.length }} pesanan pernah dibuat
                </p>
            </div>
            <Link
                :href="route('member.products.index')"
                class="inline-flex h-9 items-center gap-1.5 rounded-full bg-[#f6f6f3] px-4 text-sm font-semibold text-[#000000] transition-all hover:bg-[#000000] hover:text-white"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Pesan
            </Link>
        </div>

        <!-- Empty State -->
        <div
            v-if="orders.length === 0"
            class="flex flex-col items-center py-20"
        >
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-[#f6f6f3]">
                <svg class="h-10 w-10 text-[#c8c8c1]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M16 10a4 4 0 0 1-8 0"/><path d="M3.103 6.034h17.794"/><path d="M3.4 5.467a2 2 0 0 0-.4 1.2V20a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6.667a2 2 0 0 0-.4-1.2l-2-2.667A2 2 0 0 0 17 2H7a2 2 0 0 0-1.6.8z"/></svg>
            </div>
            <p class="mt-4 text-sm font-semibold text-[#000000]">Belum ada pesanan</p>
            <p class="mt-1 text-xs text-[#91918c]">Mulai belanja dan pesananmu akan muncul di sini</p>
            <Link
                :href="route('member.products.index')"
                class="mt-5 inline-flex h-10 items-center rounded-full bg-[#E22625] px-6 text-sm font-bold text-white transition-opacity hover:opacity-90"
            >
                Mulai Belanja
            </Link>
        </div>

        <!-- Order List Grouped by Date -->
        <div v-else class="flex flex-col gap-6">
            <div v-for="(dayOrders, dateKey) in groupedOrders" :key="dateKey">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-[#91918c]">
                    {{ dateKey }}
                </p>
                <div class="flex flex-col gap-2">
                    <div
                        v-for="order in dayOrders"
                        :key="order.id"
                        class="rounded-2xl border border-[#dadad3] bg-white transition-all hover:border-[#c8c8c1] hover:shadow-md"
                        :class="showReceipt.has(order.id) ? 'border-[#c8c8c1] shadow-md' : ''"
                    >
                        <!-- Card Header -->
                        <div class="flex items-center justify-between border-b border-[#dadad3]/50 bg-[#fbfbf9] px-4 py-2.5 rounded-t-2xl">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-[#000000]">#{{ order.id }}</span>
                                <span
                                    v-if="order.outlet"
                                    class="text-xs text-[#91918c]"
                                >{{ order.outlet.name }}</span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span
                                    v-if="order.payment_method"
                                    class="rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-[#62625b] shadow-sm"
                                >
                                    {{ paymentLabels[order.payment_method] ?? order.payment_method }}
                                </span>
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold',
                                        statusConfig[order.status]?.bg ?? 'bg-[#f6f6f3]',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'h-1.5 w-1.5 rounded-full',
                                            statusConfig[order.status]?.dot ?? 'bg-[#91918c]',
                                        ]"
                                    />
                                    {{ statusConfig[order.status]?.label ?? order.status }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="px-4 py-3">
                            <div class="space-y-1.5">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="text-xs font-medium text-[#91918c]">×{{ item.quantity }}</span>
                                        <span class="truncate text-sm font-medium text-[#000000]">{{ item.product.name }}</span>
                                    </div>
                                    <span class="shrink-0 text-xs font-semibold text-[#62625b]">{{ formatRupiah(item.subtotal) }}</span>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between border-t border-[#dadad3]/30 pt-2.5">
                                <div class="flex items-center gap-2">
                                    <svg class="h-3.5 w-3.5 text-[#91918c]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                    <span class="text-[11px] text-[#91918c]">{{ formatDate(order.created_at).time }}</span>
                                    <span v-if="order.notes" class="text-[11px] text-[#91918c]">·</span>
                                    <span v-if="order.notes" class="truncate text-[11px] text-[#91918c]">{{ order.notes }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="text-right">
                                        <template v-if="order.discount_amount > 0">
                                            <div class="text-[10px] text-green-600">Diskon -{{ formatRupiah(order.discount_amount) }}</div>
                                        </template>
                                        <span class="text-xs font-medium text-[#91918c]">Total</span>
                                        <span class="ml-1 text-sm font-bold text-[#E22625]">{{ formatRupiah(order.total_amount) }}</span>
                                    </div>
                                    <button
                                        v-if="order.status === 'completed'"
                                        @click="toggleReceipt(order.id)"
                                        class="inline-flex items-center gap-1 rounded-full border border-[#dadad3] px-2.5 py-1 text-[10px] font-semibold text-[#62625b] transition-colors hover:border-[#E22625] hover:text-[#E22625]"
                                    >
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 12h6m-3-3v6m-7 4h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        {{ showReceipt.has(order.id) ? 'Tutup' : 'Struk' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Receipt Section -->
                        <div
                            v-if="showReceipt.has(order.id)"
                            class="border-t border-[#dadad3]/50 bg-[#fbfbf9]"
                        >
                            <div class="receipt-inline">
                                <div class="receipt-header">
                                    <div class="receipt-store-name">{{ order.outlet?.name ?? 'Warung Member' }}</div>
                                    <div class="receipt-divider">- - - - - - - - - - - - - - - -</div>
                                </div>
                                <div class="receipt-body">
                                    <div class="receipt-info-row">
                                        <span>No</span>
                                        <span>#{{ order.id }}</span>
                                    </div>
                                    <div class="receipt-info-row">
                                        <span>Tgl</span>
                                        <span>{{ new Date(order.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</span>
                                    </div>
                                    <div class="receipt-info-row">
                                        <span>Bayar</span>
                                        <span>{{ paymentLabels[order.payment_method] ?? order.payment_method }}</span>
                                    </div>
                                    <div class="receipt-divider">- - - - - - - - - - - - - - - -</div>
                                    <div v-for="item in order.items" :key="item.id" class="receipt-item">
                                        <div class="receipt-item-name-qty">
                                            <span class="receipt-name">{{ item.product.name }}</span>
                                            <span class="receipt-qty">×{{ item.quantity }}</span>
                                        </div>
                                        <span class="receipt-subtotal">{{ receiptFormatRupiah(item.subtotal) }}</span>
                                    </div>
                                    <div class="receipt-divider">- - - - - - - - - - - - - - - -</div>
                                    <div v-if="order.discount_amount > 0" class="receipt-info-row receipt-discount-row">
                                        <span>Diskon</span>
                                        <span>-{{ receiptFormatRupiah(order.discount_amount) }}</span>
                                    </div>
                                    <div class="receipt-total-row">
                                        <span class="receipt-total-label">TOTAL</span>
                                        <span class="receipt-total-value">{{ receiptFormatRupiah(order.total_amount) }}</span>
                                    </div>
                                    <div v-if="order.payment_method === 'cash' && order.paid_amount != null" class="receipt-payment-section">
                                        <div class="receipt-info-row">
                                            <span>Tunai</span>
                                            <span>{{ receiptFormatRupiah(order.paid_amount) }}</span>
                                        </div>
                                        <div v-if="order.change != null" class="receipt-info-row receipt-change-row">
                                            <span>Kembali</span>
                                            <span>{{ receiptFormatRupiah(order.change) }}</span>
                                        </div>
                                    </div>
                                    <div v-if="order.payment_method === 'deposit' && order.paid_amount != null" class="receipt-payment-section">
                                        <div class="receipt-info-row">
                                            <span>Deposit</span>
                                            <span>{{ receiptFormatRupiah(order.paid_amount) }}</span>
                                        </div>
                                    </div>
                                    <div class="receipt-divider">- - - - - - - - - - - - - - - -</div>
                                    <div class="receipt-footer-text">Terima kasih</div>
                                    <div class="receipt-footer-text receipt-footer-small">Selamat berbelanja kembali</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.receipt-inline {
    font-family: 'Courier New', Courier, 'Lucida Console', monospace;
    width: 100%;
    max-width: 80mm;
    margin: 0 auto;
    padding: 3mm;
    font-size: 9px;
    line-height: 1.4;
    color: #000;
    text-transform: uppercase;
}

.receipt-header {
    text-align: center;
    margin-bottom: 2px;
}

.receipt-store-name {
    font-size: 11px;
    font-weight: bold;
    letter-spacing: 1px;
    margin-bottom: 2px;
}

.receipt-divider {
    text-align: center;
    letter-spacing: 1.5px;
    color: #555;
    font-size: 8px;
    line-height: 1.6;
}

.receipt-body {
    width: 100%;
}

.receipt-info-row {
    display: flex;
    justify-content: space-between;
    padding: 1px 0;
    font-size: 8px;
}

.receipt-item {
    display: flex;
    justify-content: space-between;
    padding: 1px 0;
    font-size: 8px;
}

.receipt-item-name-qty {
    display: flex;
    gap: 4px;
    min-width: 0;
}

.receipt-name {
    max-width: 48mm;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.receipt-qty {
    color: #555;
}

.receipt-subtotal {
    white-space: nowrap;
}

.receipt-total-row {
    display: flex;
    justify-content: space-between;
    font-size: 10px;
    font-weight: bold;
    padding: 2px 0;
}

.receipt-total-label {
    letter-spacing: 2px;
}

.receipt-payment-section {
    margin-top: 2px;
}

.receipt-change-row {
    font-weight: bold;
}

.receipt-footer-text {
    text-align: center;
    font-size: 8px;
    letter-spacing: 1px;
    padding: 1px 0;
}

.receipt-footer-small {
    font-size: 7px;
    color: #555;
}
</style>
