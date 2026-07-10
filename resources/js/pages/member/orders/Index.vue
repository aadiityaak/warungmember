<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useCart } from '@/composables/useCart';
import MemberLayout from '@/layouts/MemberLayout.vue';

defineOptions({ layout: MemberLayout });

const page = usePage();
const paymentSettings = computed(() => (page.props.payment as Record<string, any>) ?? {});

interface VoucherData {
    id: number;
    voucher_id: number;
    status: string;
    voucher: {
        id: number;
        name: string;
        description: string | null;
        discount_type: 'percent' | 'fixed';
        discount_value: number;
        max_discount: number | null;
        min_purchase: number | null;
        is_active: boolean;
    };
}

const { outlets, lastOutletId, depositBalance, activeVouchers } = defineProps<{
    outlets: Array<{ id: number; name: string; address: string | null }>;
    lastOutletId: number | null;
    depositBalance: number;
    activeVouchers: VoucherData[];
}>();

const cart = useCart();
const selectedOutlet = ref<number | null>(lastOutletId ?? outlets?.[0]?.id ?? null);
const paymentMethod = ref<string>('');
const notes = ref('');
const selectedVoucherId = ref<number | null>(null);
const errors = ref<Record<string, string>>({});
const submitting = ref(false);
const voucherModalOpen = ref(false);

const canCheckout = computed(() => selectedOutlet.value && paymentMethod.value);

// Copy norek
const copiedIndex = ref<number | null>(null);

function copyNorek(norek: string, index: number) {
    navigator.clipboard.writeText(norek);
    copiedIndex.value = index;
    setTimeout(() => { copiedIndex.value = null; }, 2000);
}

// Download QRIS
function downloadQris(url: string) {
    const a = document.createElement('a');
    a.href = url;
    a.download = 'qris-wm.jpg';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

// Discount calculation
const selectedVoucher = computed(() => {
    if (!selectedVoucherId.value) return null;
    return activeVouchers.find((mv) => mv.id === selectedVoucherId.value) ?? null;
});

const discount = computed(() => {
    const sv = selectedVoucher.value;
    if (!sv) return 0;
    const total = cart.totalAmount();
    const v = sv.voucher;

    if (v.min_purchase && total < v.min_purchase) return 0;

    if (v.discount_type === 'percent') {
        let d = Math.round(total * v.discount_value / 100);
        if (v.max_discount && d > v.max_discount) d = v.max_discount;
        return d;
    }
    return v.discount_value;
});

const finalTotal = computed(() => Math.max(0, cart.totalAmount() - discount.value));

// Reset voucher if it becomes invalid (min_purchase not met)
watch([() => cart.totalAmount(), selectedVoucherId], ([total, vid]) => {
    if (!vid) return;
    const sv = activeVouchers.find((mv) => mv.id === vid);
    if (sv?.voucher.min_purchase && total < sv.voucher.min_purchase) {
        selectedVoucherId.value = null;
    }
});

const paymentMethods = [
    { value: 'cash', label: 'Tunai', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' },
    { value: 'qris', label: 'QRIS', icon: 'M5 3h4v4H5zM16 3h4v4h-4zM5 16h4v4H5zM21 16h-3a2 2 0 0 0-2 2v3M21 21v.01M12 7v3a2 2 0 0 1-2 2H7M3 12h.01M12 3h.01M12 16v.01M16 12h1M21 12v.01M12 21v-1' },
    { value: 'transfer', label: 'Transfer', icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4' },
    { value: 'deposit', label: 'Deposit', icon: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm0 13c-2.33 0-4.31-1.46-5.11-3.5h10.22c-.8 2.04-2.78 3.5-5.11 3.5z' },
];

function submit() {
    if (!canCheckout.value) return;
    const payload: Record<string, unknown> = {
        outlet_id: selectedOutlet.value,
        payment_method: paymentMethod.value,
        items: cart.items.map((i) => ({ product_id: i.product_id, quantity: i.quantity })),
        notes: notes.value,
    };
    if (selectedVoucherId.value) {
        payload.member_voucher_id = selectedVoucherId.value;
    }
    submitting.value = true;
    router.post(route('member.orders.store'), payload, {
        onSuccess: () => {
            cart.clear();
            notes.value = '';
            selectedVoucherId.value = null;
            submitting.value = false;
        },
        onError: (err) => {
            errors.value = err as Record<string, string>;
            submitting.value = false;
        },
    });
}
</script>

<template>
    <Head title="Pesanan Saya" />

    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold leading-[1.2] text-[#000000]">Pesanan Saya</h1>
            </div>
            <button
                v-if="cart.items.length"
                @click="cart.clear()"
                class="text-[#E22625] hover:opacity-80 cursor-pointer"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </button>
        </div>

        <!-- Cart Section -->
        <div v-if="cart.items.length" class="rounded-2xl border border-[#dadad3] bg-white p-4">
            <h2 class="text-sm font-bold leading-[1.4] text-[#000000] mb-3">Keranjang</h2>

            <!-- Outlet Selector -->
            <div class="mb-3">
                <label class="text-xs font-semibold text-[#000000] mb-1.5 block">Lokasi Pengambilan</label>
                <select
                    v-model="selectedOutlet"
                    class="w-full rounded-xl border border-[#dadad3] bg-[#f6f6f3] px-3 py-2.5 text-sm leading-[1.4] text-[#000000] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#E22625]"
                >
                    <option :value="null" disabled>-- Pilih Outlet --</option>
                    <option v-for="o in outlets" :key="o.id" :value="o.id">{{ o.name }}</option>
                </select>
                <p v-if="errors.outlet_id" class="text-xs text-red-500 mt-1">{{ errors.outlet_id }}</p>
            </div>

            <!-- Payment Method -->
            <div class="mb-3">
                <label class="text-xs font-semibold text-[#000000] mb-1.5 block">Metode Pembayaran</label>
                <div class="grid grid-cols-4 gap-2">
                    <button
                        v-for="pm in paymentMethods"
                        :key="pm.value"
                        type="button"
                        @click="paymentMethod = pm.value"
                        :class="[
                            'flex flex-col items-center gap-1 rounded-xl border px-2 py-2.5 text-xs font-semibold transition-colors',
                            paymentMethod === pm.value
                                ? 'border-[#E22625] bg-[#E22625]/10 text-[#E22625]'
                                : 'border-[#dadad3] bg-[#f6f6f3] text-[#62625b] hover:border-[#c8c8c1]',
                        ]"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="pm.icon" /></svg>
                        {{ pm.label }}
                    </button>
                </div>
                <p v-if="errors.payment_method" class="text-xs text-red-500 mt-1">{{ errors.payment_method }}</p>
            </div>

            <!-- Payment Info: Bank / Transfer -->
            <div v-if="paymentMethod === 'transfer'" class="mb-3 rounded-xl border border-[#dadad3] bg-[#f6f6f3] p-3">
                <p class="text-xs font-semibold text-[#62625b] mb-2">Transfer ke Rekening</p>
                <div v-if="paymentSettings.banks?.length" class="space-y-2">
                    <div
                        v-for="(bank, i) in paymentSettings.banks.filter((b: any) => b.enabled)"
                        :key="i"
                        class="rounded-lg bg-white px-3 py-2"
                    >
                        <p class="text-sm font-bold text-[#000000]">{{ bank.bank_name }}</p>
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs text-[#62625b] font-mono">{{ bank.account_number }}</p>
                            <button
                                type="button"
                                @click="copyNorek(bank.account_number, i)"
                                class="shrink-0 rounded-full border border-[#dadad3] px-2.5 py-1 text-xs font-semibold text-[#000000] hover:bg-[#f6f6f3] transition-colors"
                            >
                                {{ copiedIndex === i ? 'Tersalin' : 'Salin' }}
                            </button>
                        </div>
                        <p class="text-xs text-[#91918c]">a.n. {{ bank.account_name }}</p>
                    </div>
                </div>
                <p v-else class="text-xs text-[#91918c]">Belum ada rekening yang dikonfigurasi.</p>
            </div>

            <!-- Payment Info: QRIS -->
            <div v-if="paymentMethod === 'qris'" class="mb-3 rounded-xl border border-[#dadad3] bg-[#f6f6f3] p-3">
                <p class="text-xs font-semibold text-[#62625b] mb-2">Scan QRIS</p>
                <div v-if="paymentSettings.qris?.qr_image" class="flex flex-col items-center">
                    <img
                        :src="paymentSettings.qris.qr_image"
                        alt="QRIS"
                        class="h-48 w-48 rounded-xl object-contain bg-white border border-[#dadad3]"
                    />
                    <button
                        type="button"
                        @click="downloadQris(paymentSettings.qris.qr_image)"
                        class="mt-2 rounded-full border border-[#dadad3] px-3 py-1.5 text-xs font-semibold text-[#000000] hover:bg-[#f6f6f3] transition-colors"
                    >
                        Unduh QRIS
                    </button>
                    <p v-if="paymentSettings.qris.merchant_name" class="mt-2 text-sm font-bold text-[#000000]">
                        {{ paymentSettings.qris.merchant_name }}
                    </p>
                    <p v-if="paymentSettings.qris.merchant_id" class="text-xs text-[#91918c]">
                        MID: {{ paymentSettings.qris.merchant_id }}
                    </p>
                </div>
                <p v-else class="text-xs text-[#91918c]">QRIS belum dikonfigurasi.</p>
            </div>

            <!-- Deposit Info -->
            <div v-if="paymentMethod === 'deposit'" class="mb-3 rounded-xl border border-[#dadad3] bg-[#f6f6f3] p-3">
                <p class="text-xs font-semibold text-[#62625b]">Saldo Deposit Kamu</p>
                <p class="mt-1 text-lg font-bold text-[#000000]">Rp{{ depositBalance.toLocaleString('id-ID') }}</p>
                <p
                    v-if="depositBalance < finalTotal"
                    class="mt-1 text-xs text-red-500"
                >
                    Saldo tidak cukup untuk total akhir Rp{{ finalTotal.toLocaleString('id-ID') }}
                </p>
                <p
                    v-else-if="finalTotal > 0"
                    class="mt-1 text-xs text-green-600"
                >
                    Saldo cukup
                </p>
                <p v-if="errors.payment_method" class="mt-1 text-xs text-red-500">{{ errors.payment_method }}</p>
            </div>

            <!-- Voucher -->
            <div class="mb-3">
                <label class="text-xs font-semibold text-[#000000] mb-1.5 block">Voucher Diskon</label>
                <button
                    type="button"
                    @click="voucherModalOpen = true"
                    class="w-full rounded-xl border border-[#dadad3] bg-[#f6f6f3] px-3 py-2.5 text-sm leading-[1.4] text-left flex items-center justify-between focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#E22625]"
                >
                    <span v-if="selectedVoucher" class="text-[#000000] font-medium">{{ selectedVoucher.voucher.name }}</span>
                    <span v-else-if="activeVouchers.length === 0" class="text-[#91918c]">Belum ada voucher tersedia</span>
                    <span v-else class="text-[#91918c]">Ketuk untuk pilih voucher</span>
                    <svg class="h-4 w-4 text-[#91918c] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <p v-if="selectedVoucher && discount > 0" class="mt-1 text-xs text-green-600">
                    Diskon: -Rp{{ discount.toLocaleString('id-ID') }}
                </p>
                <p v-else-if="selectedVoucher && discount === 0" class="mt-1 text-xs text-red-500">
                    Total belanja belum mencapai minimum pembelian
                </p>
                <p v-if="errors.member_voucher_id" class="text-xs text-red-500 mt-1">{{ errors.member_voucher_id }}</p>
            </div>

            <!-- Modal Pilih Voucher -->
            <Teleport to="body">
                <div v-if="voucherModalOpen" class="fixed inset-0 z-50 flex items-end justify-center" @click.self="voucherModalOpen = false">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-black/40" @click="voucherModalOpen = false" />
                    <!-- Sheet -->
                    <div class="relative z-10 w-full max-w-md rounded-t-2xl bg-white pb-6 shadow-xl animate-slide-up">
                        <div class="flex items-center justify-between px-5 pt-4 pb-2">
                            <h3 class="text-base font-bold text-[#000000]">Pilih Voucher</h3>
                            <button @click="voucherModalOpen = false" class="p-1 text-[#91918c] hover:text-[#000000]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="max-h-64 overflow-y-auto px-5">
                            <div v-if="activeVouchers.length === 0" class="py-8 text-center">
                                <p class="text-sm text-[#91918c]">Belum ada voucher tersedia</p>
                            </div>
                            <div
                                v-for="mv in activeVouchers"
                                :key="mv.id"
                                @click="selectedVoucherId = mv.id; voucherModalOpen = false"
                                class="flex items-center justify-between rounded-xl border px-4 py-3 mb-2 cursor-pointer transition-colors"
                                :class="selectedVoucherId === mv.id
                                    ? 'border-[#E22625] bg-[#E22625]/5'
                                    : 'border-[#dadad3] hover:bg-[#f6f6f3]'"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-[#000000]">{{ mv.voucher.name }}</p>
                                    <p class="text-xs text-[#91918c] mt-0.5">
                                        {{ mv.voucher.discount_type === 'percent' ? `Diskon ${mv.voucher.discount_value}%` : `Diskon Rp${Number(mv.voucher.discount_value).toLocaleString('id-ID')}` }}
                                        {{ mv.voucher.max_discount ? ` (maks. Rp${Number(mv.voucher.max_discount).toLocaleString('id-ID')})` : '' }}
                                    </p>
                                    <p v-if="mv.voucher.min_purchase" class="text-xs text-[#91918c]">
                                        Min. belanja Rp{{ Number(mv.voucher.min_purchase).toLocaleString('id-ID') }}
                                    </p>
                                </div>
                                <div v-if="selectedVoucherId === mv.id" class="shrink-0 ml-2">
                                    <svg class="h-5 w-5 text-[#E22625]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 px-5">
                            <button
                                @click="selectedVoucherId = null; voucherModalOpen = false"
                                class="w-full rounded-full border border-[#dadad3] py-2.5 text-sm font-semibold text-[#000000] hover:bg-[#f6f6f3] transition-colors"
                            >
                                {{ selectedVoucherId ? 'Gunakan tanpa voucher' : 'Tutup' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Teleport>

            <!-- Cart Items -->
            <div class="flex flex-col gap-2">
                <div
                    v-for="item in cart.items"
                    :key="item.product_id"
                    class="flex items-center gap-2 rounded-xl bg-[#f6f6f3] p-2"
                >
                    <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-white">
                        <img v-if="item.image" :src="item.image" :alt="item.name" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full w-full items-center justify-center">
                            <svg class="h-5 w-5 text-[#dadad3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold leading-[1.3] text-[#000000] truncate">{{ item.name }}</p>
                        <div class="flex items-center justify-between mt-0.5">
                            <span class="text-xs font-bold text-[#E22625]">Rp{{ item.current_price.toLocaleString('id-ID') }}</span>
                            <div class="flex items-center gap-1.5">
                                <button
                                    @click="cart.updateQty(item.product_id, item.quantity - 1)"
                                    class="flex h-6 w-6 items-center justify-center rounded-full border border-[#dadad3] text-xs font-bold text-[#000000] hover:bg-white"
                                >-</button>
                                <span class="w-5 text-center text-xs font-semibold">{{ item.quantity }}</span>
                                <button
                                    @click="cart.updateQty(item.product_id, item.quantity + 1)"
                                    class="flex h-6 w-6 items-center justify-center rounded-full border border-[#dadad3] text-xs font-bold text-[#000000] hover:bg-white"
                                >+</button>
                            </div>
                        </div>
                    </div>
                    <button @click="cart.remove(item.product_id)" class="p-1 text-[#91918c] hover:text-[#E22625]">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <!-- Notes + Checkout -->
            <div class="mt-3 space-y-2">
                <textarea
                    v-model="notes"
                    rows="2"
                    placeholder="Catatan (opsional)..."
                    class="w-full rounded-xl border border-[#dadad3] bg-[#f6f6f3] px-3 py-2 text-xs leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#E22625]"
                />
                <div class="space-y-1">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-[#62625b]">Total</span>
                        <span class="font-semibold text-[#000000]">Rp{{ cart.totalAmount().toLocaleString('id-ID') }}</span>
                    </div>
                    <div v-if="discount > 0" class="flex items-center justify-between text-sm">
                        <span class="text-[#62625b]">Diskon</span>
                        <span class="font-semibold text-green-600">-Rp{{ discount.toLocaleString('id-ID') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-[#dadad3] pt-1">
                        <span class="text-sm font-bold text-[#000000]">Total Akhir</span>
                        <span class="text-sm font-bold text-[#E22625]">Rp{{ finalTotal.toLocaleString('id-ID') }}</span>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <div />
                    <button
                        @click="submit"
                        :disabled="submitting || !canCheckout"
                        class="inline-flex h-9 items-center rounded-full px-5 text-sm font-bold text-white transition-all"
                        :class="canCheckout ? 'bg-[#E22625] hover:opacity-90' : 'bg-[#dadad3] cursor-not-allowed'"
                    >
                        Pesan Sekarang
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty Cart -->
        <div v-if="cart.items.length === 0" class="py-16 text-center">
            <svg class="mx-auto h-16 w-16 text-[#dadad3]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 10a4 4 0 0 1-8 0"/><path d="M3.103 6.034h17.794"/><path d="M3.4 5.467a2 2 0 0 0-.4 1.2V20a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6.667a2 2 0 0 0-.4-1.2l-2-2.667A2 2 0 0 0 17 2H7a2 2 0 0 0-1.6.8z"/></svg>
            <p class="mt-4 text-sm text-[#91918c]">Belum ada produk di keranjang belanja</p>
            <a
                :href="route('member.products.index')"
                class="mt-3 inline-flex h-9 items-center rounded-full bg-[#E22625] px-5 text-sm font-bold text-white"
            >
                Lihat Menu
            </a>
        </div>

        <!-- Link ke Riwayat Order -->
        <a
            :href="route('member.orders.history')"
            class="flex items-center justify-between rounded-2xl border border-[#dadad3] bg-white px-4 py-3.5 transition-colors hover:bg-[#fbfbf9]"
        >
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f6f6f3]">
                    <svg class="h-4 w-4 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold leading-[1.4] text-[#000000]">Riwayat Order</p>
                    <p class="text-xs leading-[1.4] text-[#62625b]">Lihat semua pesanan kamu</p>
                </div>
            </div>
            <svg class="h-4 w-4 shrink-0 text-[#91918c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </a>
    </div>
</template>

<style scoped>
@keyframes slide-up {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}

.animate-slide-up {
    animation: slide-up 0.25s ease-out;
}
</style>
