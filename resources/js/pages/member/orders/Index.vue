<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useCart } from '@/composables/useCart';
import MemberLayout from '@/layouts/MemberLayout.vue';

defineOptions({ layout: MemberLayout });

const { orders, outlets } = defineProps<{
    orders: Array<Record<string, any>>;
    outlets: Array<{ id: number; name: string; address: string | null }>;
}>();

const cart = useCart();
const selectedOutlet = ref<number | null>(outlets?.[0]?.id ?? null);
const paymentMethod = ref<string>('');
const notes = ref('');
const errors = ref<Record<string, string>>({});
const submitting = ref(false);

const canCheckout = computed(() => selectedOutlet.value && paymentMethod.value);

const paymentMethods = [
    { value: 'cash', label: 'Tunai', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' },
    { value: 'qris', label: 'QRIS', icon: 'M12 4v1m6 11h2m-6 0h-2m4 0v-2a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4-4h2a2 2 0 002-2V5a2 2 0 00-2-2h-2v2M4 17h.01M4 12h.01M4 7h.01' },
    { value: 'transfer', label: 'Transfer', icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4' },
];

function submit() {
    if (!canCheckout.value) return;
    const payload = {
        outlet_id: selectedOutlet.value,
        payment_method: paymentMethod.value,
        items: cart.items.map((i) => ({ product_id: i.product_id, quantity: i.quantity })),
        notes: notes.value,
    };
    submitting.value = true;
    router.post(route('member.orders.store'), payload, {
        onSuccess: () => {
            cart.clear();
            notes.value = '';
            submitting.value = false;
        },
        onError: (err) => {
            errors.value = err as Record<string, string>;
            submitting.value = false;
        },
    });
}

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
};
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
                class="text-sm font-semibold text-[#E22625]"
            >
                Kosongkan Keranjang
            </button>
        </div>

        <!-- Cart Section -->
        <div v-if="cart.items.length" class="rounded-2xl border border-[#dadad3] bg-white p-4">
            <h2 class="text-sm font-bold leading-[1.4] text-[#000000] mb-3">Keranjang ({{ cart.totalItems() }} item)</h2>

            <!-- Outlet Selector -->
            <div class="mb-3">
                <label class="text-xs font-semibold text-[#000000] mb-1.5 block">Pilih Outlet</label>
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
                <div class="grid grid-cols-3 gap-2">
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
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold text-[#E22625]">Rp{{ cart.totalAmount().toLocaleString('id-ID') }}</p>
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
        <div v-if="cart.items.length === 0 && orders.length === 0" class="py-16 text-center">
            <svg class="mx-auto h-16 w-16 text-[#dadad3]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 10a4 4 0 0 1-8 0"/><path d="M3.103 6.034h17.794"/><path d="M3.4 5.467a2 2 0 0 0-.4 1.2V20a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6.667a2 2 0 0 0-.4-1.2l-2-2.667A2 2 0 0 0 17 2H7a2 2 0 0 0-1.6.8z"/></svg>
            <p class="mt-4 text-sm text-[#91918c]">Belum ada produk di keranjang belanja</p>
            <a
                :href="route('member.products.index')"
                class="mt-3 inline-flex h-9 items-center rounded-full bg-[#E22625] px-5 text-sm font-bold text-white"
            >
                Lihat Menu
            </a>
        </div>

        <!-- Order History -->
        <div v-if="orders.length" class="flex flex-col gap-3">
            <h2 class="text-lg font-semibold leading-[1.3] text-[#000000]">Riwayat Pesanan</h2>
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
