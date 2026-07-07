<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { useCart } from '@/composables/useCart';

defineOptions({ layout: null });

const cart = useCart();
const form = useForm({
    items: cart.items.map((i) => ({ product_id: i.product_id, quantity: i.quantity })),
    notes: '',
});

function submit() {
    form.items = cart.items.map((i) => ({ product_id: i.product_id, quantity: i.quantity }));
    form.post(route('member.orders.store'), {
        onSuccess: () => cart.clear(),
    });
}

const statusLabels: Record<string, string> = {
    pending: 'Menunggu',
    processing: 'Diproses',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
};
</script>

<template>
    <Head title="Keranjang" />

    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold leading-[1.2] text-[#000000]">Keranjang</h1>
                <p class="mt-0.5 text-sm leading-[1.4] text-[#62625b]">{{ cart.totalItems() }} item</p>
            </div>
            <button
                v-if="cart.items.length"
                @click="cart.clear()"
                class="text-sm font-semibold text-[#E22625]"
            >
                Kosongkan
            </button>
        </div>

        <!-- Empty -->
        <div v-if="cart.items.length === 0" class="py-16 text-center">
            <svg class="mx-auto h-16 w-16 text-[#dadad3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" /></svg>
            <p class="mt-4 text-sm text-[#91918c]">Keranjang masih kosong</p>
            <a
                :href="route('member.products.index')"
                class="mt-3 inline-flex h-9 items-center rounded-full bg-[#E22625] px-5 text-sm font-bold text-white"
            >
                Lihat Menu
            </a>
        </div>

        <!-- Cart Items -->
        <div v-else class="flex flex-col gap-3">
            <div
                v-for="item in cart.items"
                :key="item.product_id"
                class="flex items-center gap-3 rounded-2xl border border-[#dadad3] bg-white p-3"
            >
                <!-- Image -->
                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-[#f6f6f3]">
                    <img
                        v-if="item.image"
                        :src="item.image"
                        :alt="item.name"
                        class="h-full w-full object-cover"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center">
                        <svg class="h-6 w-6 text-[#dadad3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold leading-[1.3] text-[#000000] truncate">{{ item.name }}</h3>
                    <p class="text-sm font-bold text-[#E22625]">Rp{{ item.current_price.toLocaleString('id-ID') }}</p>
                </div>

                <!-- Qty Controls -->
                <div class="flex items-center gap-2">
                    <button
                        @click="cart.updateQty(item.product_id, item.quantity - 1)"
                        class="flex h-7 w-7 items-center justify-center rounded-full border border-[#dadad3] text-sm font-bold text-[#000000] hover:bg-[#f6f6f3]"
                    >
                        -
                    </button>
                    <span class="w-6 text-center text-sm font-semibold text-[#000000]">{{ item.quantity }}</span>
                    <button
                        @click="cart.updateQty(item.product_id, item.quantity + 1)"
                        class="flex h-7 w-7 items-center justify-center rounded-full border border-[#dadad3] text-sm font-bold text-[#000000] hover:bg-[#f6f6f3]"
                    >
                        +
                    </button>
                </div>

                <!-- Delete -->
                <button
                    @click="cart.remove(item.product_id)"
                    class="flex h-7 w-7 items-center justify-center rounded-full text-[#91918c] hover:bg-[#f6f6f3] hover:text-[#E22625]"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </div>
        </div>

        <!-- Checkout -->
        <div v-if="cart.items.length" class="rounded-2xl border border-[#dadad3] bg-white p-4">
            <form @submit.prevent="submit" class="space-y-3">
                <div>
                    <label class="text-sm font-semibold text-[#000000]">Catatan (opsional)</label>
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        placeholder="Contoh: sambel dipisah, es teh kurang manis..."
                        class="mt-1 w-full rounded-xl border border-[#dadad3] bg-[#f6f6f3] px-3 py-2 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#E22625]"
                    />
                </div>

                <div class="flex items-center justify-between border-t border-[#dadad3] pt-3">
                    <div>
                        <p class="text-xs text-[#91918c]">Total Pembayaran</p>
                        <p class="text-lg font-bold text-[#E22625]">Rp{{ cart.totalAmount().toLocaleString('id-ID') }}</p>
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex h-10 items-center rounded-full bg-[#E22625] px-6 text-sm font-bold text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                    >
                        Pesan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
