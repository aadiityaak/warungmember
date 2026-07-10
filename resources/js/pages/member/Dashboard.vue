<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useCart } from '@/composables/useCart';

defineOptions({
    layout: MemberLayout,
});

const { stats, products } = defineProps<{
    stats: {
        total_points: number;
        deposit_balance: number;
    };
    products: Array<Record<string, any>>;
}>();

const banners = [
    '/banner/1.jpg',
    '/banner/2.jpg',
    '/banner/3.jpg',
];

const current = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

function next() {
    current.value = (current.value + 1) % banners.length;
}

function prev() {
    current.value = (current.value - 1 + banners.length) % banners.length;
}

function goTo(i: number) {
    current.value = i;
    resetTimer();
}

function resetTimer() {
    if (timer) clearInterval(timer);
    timer = setInterval(next, 4000);
}

onMounted(() => resetTimer());
onUnmounted(() => { if (timer) clearInterval(timer); });

const cart = useCart();

function addToCart(product: Record<string, any>) {
    cart.add({
        id: product.id,
        name: product.name,
        price: product.price,
        current_price: product.current_price,
        image: product.image,
    });
}

function isInCart(productId: number): boolean {
    return cart.items.some((i) => i.product_id === productId);
}

function formatRupiah(n: number): string {
    return 'Rp ' + n.toLocaleString('id-ID');
}
</script>

<template>
    <div class="flex flex-col gap-6">
        <!-- Welcome -->
        <div class="flex items-center justify-between">
            <h2 class="text-[22px] font-semibold leading-[1.25] text-[#000000]">
                Halo<span class="wave-hand">👋</span>
            </h2>
            <Link
                :href="route('member.outlets.index')"
                class="inline-flex items-center gap-1 rounded-full bg-[#f6f6f3] px-3 py-1.5 text-xs font-semibold text-[#000000] transition-colors hover:bg-[#000000] hover:text-white"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M15 21v-5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v5"/><path d="M17.774 10.31a1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.451 0 1.12 1.12 0 0 0-1.548 0 2.5 2.5 0 0 1-3.452 0 1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.77-3.248l2.889-4.184A2 2 0 0 1 7 2h10a2 2 0 0 1 1.653.873l2.895 4.192a2.5 2.5 0 0 1-3.774 3.244"/><path d="M4 10.95V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8.05"/></svg>
                Outlet
            </Link>
        </div>

        <!-- Banner Carousel -->
        <div class="relative overflow-hidden rounded-2xl">
            <div
                class="flex transition-transform duration-500 ease-out"
                :style="{ transform: `translateX(-${current * 100}%)` }"
            >
                <img
                    v-for="(src, i) in banners"
                    :key="i"
                    :src="src"
                    :alt="`Banner ${i + 1}`"
                    class="w-full shrink-0 object-cover"
                />
            </div>

            <!-- Dots -->
            <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-1.5">
                <button
                    v-for="(_, i) in banners"
                    :key="i"
                    @click="goTo(i)"
                    class="h-2 rounded-full transition-all"
                    :class="i === current
                        ? 'w-5 bg-white'
                        : 'w-2 bg-white/50 hover:bg-white/80'
                    "
                />
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="flex flex-col gap-2">
            <!-- Poin -->
            <div class="flex items-center justify-between rounded-2xl bg-[#f6f6f3] px-4 py-4">
                <div>
                    <p class="text-sm leading-[1.4] text-[#62625b]">Total Poin</p>
                    <p class="mt-0.5 text-[22px] font-semibold leading-[1.25] text-[#000000]">
                        {{ stats.total_points.toLocaleString('id-ID') }}
                    </p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white">
                    <svg class="h-5 w-5 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Saldo -->
            <div class="flex items-center justify-between rounded-2xl bg-[#f6f6f3] px-4 py-4">
                <div>
                    <p class="text-sm leading-[1.4] text-[#62625b]">Saldo Deposit</p>
                    <p class="mt-0.5 text-[22px] font-semibold leading-[1.25] text-[#000000]">
                        {{ formatRupiah(stats.deposit_balance) }}
                    </p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white">
                    <svg class="h-5 w-5 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div>
            <h3 class="text-lg font-semibold leading-[1.3] text-[#000000]">Aksi Cepat</h3>
            <div class="mt-3 flex flex-wrap gap-2">
                <Link
                    :href="route('member.rewards')"
                    class="inline-flex items-center gap-1.5 rounded-full bg-[#f6f6f3] px-4 py-2 text-sm font-semibold leading-[1.4] text-[#000000] transition-colors hover:bg-[#000000] hover:text-white"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    Tukar Reward
                </Link>
                <Link
                    :href="route('member.vouchers')"
                    class="inline-flex items-center gap-1.5 rounded-full bg-[#f6f6f3] px-4 py-2 text-sm font-semibold leading-[1.4] text-[#000000] transition-colors hover:bg-[#000000] hover:text-white"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Voucher Saya
                </Link>
            </div>
        </div>

        <!-- Produk Terbaru -->
        <div v-if="products.length">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold leading-[1.3] text-[#000000]">Menu Favorit</h3>
                <Link
                    :href="route('member.products.index')"
                    class="text-sm font-semibold text-[#E22625]"
                >
                    Lihat Semua
                </Link>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-3">
                <div
                    v-for="product in products.slice(0, 6)"
                    :key="product.id"
                    class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white transition-shadow hover:shadow-md"
                >
                    <div class="relative aspect-square overflow-hidden bg-[#f6f6f3]">
                        <img
                            v-if="product.image"
                            :src="product.image"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center">
                            <svg class="h-10 w-10 text-[#dadad3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <div v-if="product.is_on_discount" class="absolute left-2 top-2 rounded-full bg-[#E22625] px-2 py-0.5 text-[10px] font-bold text-white">
                            DISKON
                        </div>
                    </div>
                    <div class="p-3">
                        <h4 class="text-sm font-semibold leading-[1.3] text-[#000000] line-clamp-2">{{ product.name }}</h4>
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="text-sm font-bold text-[#E22625]">Rp{{ product.current_price.toLocaleString('id-ID') }}</span>
                            <span v-if="product.is_on_discount" class="text-xs text-[#91918c] line-through">Rp{{ product.price.toLocaleString('id-ID') }}</span>
                        </div>
                        <button
                            @click="addToCart(product)"
                            class="mt-2 flex w-full items-center justify-center gap-1 rounded-full py-1.5 text-xs font-bold transition-colors"
                            :class="
                                isInCart(product.id)
                                    ? 'bg-[#E22625]/10 text-[#E22625]'
                                    : 'bg-[#f6f6f3] text-[#000000] hover:bg-[#E22625] hover:text-white'
                            "
                        >
                            <svg v-if="isInCart(product.id)" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            {{ isInCart(product.id) ? 'Di Keranjang' : '+ Keranjang' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.wave-hand {
    display: inline-block;
    animation: wave 1s ease-in-out infinite;
    transform-origin: 70% 70%;
}

@keyframes wave {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(20deg); }
    50% { transform: rotate(0deg); }
    75% { transform: rotate(15deg); }
}
</style>
