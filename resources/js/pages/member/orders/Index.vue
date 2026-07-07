<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useCart } from '@/composables/useCart';
import MemberLayout from '@/layouts/MemberLayout.vue';

defineOptions({ layout: MemberLayout });

const refreshing = ref(false);

function reload() {
    refreshing.value = true;
    router.reload({
        only: ['orders'],
        onFinish: () => { refreshing.value = false; },
    });
}

const { orders } = defineProps<{
    orders: Array<Record<string, any>>;
}>();

const cart = useCart();
const notes = ref('');
const errors = ref<Record<string, string>>({});
const submitting = ref(false);

function submit() {
    const payload = {
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
                <p v-if="errors.notes" class="text-xs text-red-500">{{ errors.notes }}</p>
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold text-[#E22625]">Rp{{ cart.totalAmount().toLocaleString('id-ID') }}</p>
                    <button
                        @click="submit"
                        :disabled="submitting"
                        class="inline-flex h-9 items-center rounded-full bg-[#E22625] px-5 text-sm font-bold text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                    >
                        Pesan Sekarang
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty Cart -->
        <div v-else-if="orders.length === 0" class="py-16 text-center">
            <svg class="mx-auto h-16 w-16 text-[#dadad3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" /></svg>
            <p class="mt-4 text-sm text-[#91918c]">Belum ada pesanan</p>
            <a
                :href="route('member.products.index')"
                class="mt-3 inline-flex h-9 items-center rounded-full bg-[#E22625] px-5 text-sm font-bold text-white"
            >
                Lihat Menu
            </a>
        </div>

        <!-- Order History -->
        <div v-if="orders.length" class="flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold leading-[1.3] text-[#000000]">Riwayat Pesanan</h2>
                <button
                    @click="reload"
                    :disabled="refreshing"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full text-[#91918c] transition-all hover:bg-[#f6f6f3] hover:text-[#000000] disabled:opacity-50"
                >
                    <svg :class="['h-4 w-4', refreshing && 'animate-spin']" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                </button>
            </div>
            <div
                v-for="order in orders"
                :key="order.id"
                class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white"
            >
                <div class="flex items-center justify-between border-b border-[#dadad3] bg-[#fbfbf9] px-4 py-2.5">
                    <div>
                        <p class="text-xs leading-[1.4] text-[#91918c]">
                            {{ new Date(order.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
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
