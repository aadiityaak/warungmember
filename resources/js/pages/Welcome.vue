<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: MemberLayout });

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
</script>

<template>
    <Head title="Warung Mas Mbull" />

    <div class="flex flex-col gap-6">
        <!-- Welcome -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[22px] font-semibold leading-[1.25] text-[#000000]">
                    Halo, Selamat Datang!
                </h2>
                <p class="mt-1 text-sm leading-[1.4] text-[#62625b]">
                    Nikmati program loyalitas dan promo spesial dari kami.
                </p>
            </div>
            <Link
                :href="route('login')"
                class="inline-flex shrink-0 items-center gap-1 rounded-full bg-[#f6f6f3] px-3 py-1.5 text-xs font-semibold text-[#000000] transition-colors hover:bg-[#000000] hover:text-white"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Cari Outlet
            </Link>
        </div>

        <!-- Banner Carousel -->
        <div class="relative overflow-hidden rounded-2xl">
            <button
                @click="prev"
                class="absolute left-2 top-1/2 z-10 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-white/80 text-[#000000] shadow transition-colors hover:bg-white"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button
                @click="next"
                class="absolute right-2 top-1/2 z-10 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-white/80 text-[#000000] shadow transition-colors hover:bg-white"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

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

        <!-- CTA -->
        <div class="rounded-2xl bg-gradient-to-br from-[#e60023] to-[#cc001f] px-5 py-6 text-white">
            <h3 class="text-lg font-bold leading-[1.3]">Gabung Sekarang, Mulai Kumpulkan Poin!</h3>
            <p class="mt-1.5 text-sm leading-[1.4] text-white/80">
                Dapatkan poin setiap transaksi, tukarkan dengan reward menarik, dan nikmati voucher spesial khusus member.
            </p>
            <div class="mt-4 flex gap-2">
                <Link
                    :href="route('register')"
                    class="inline-flex rounded-full bg-white px-5 py-2 text-sm font-bold text-[#e60023] transition-colors hover:bg-[#f6f6f3]"
                >
                    Daftar Gratis
                </Link>
                <Link
                    :href="route('login')"
                    class="inline-flex rounded-full bg-white/20 px-5 py-2 text-sm font-semibold text-white transition-colors hover:bg-white/30"
                >
                    Login
                </Link>
            </div>
        </div>

        <!-- Fitur -->
        <div>
            <h3 class="text-lg font-semibold leading-[1.3] text-[#000000]">Fitur WarungMember</h3>
            <div class="mt-3 grid grid-cols-2 gap-2">
                <div class="rounded-2xl bg-[#f6f6f3] px-4 py-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white">
                        <svg class="h-5 w-5 text-[#E22625]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-[#000000]">Kumpulkan Poin</p>
                    <p class="mt-0.5 text-xs text-[#62625b]">Poin bertambah tiap transaksi.</p>
                </div>
                <div class="rounded-2xl bg-[#f6f6f3] px-4 py-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white">
                        <svg class="h-5 w-5 text-[#E22625]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-[#000000]">Tukar Reward</p>
                    <p class="mt-0.5 text-xs text-[#62625b]">Tukarkan poin dengan hadiah.</p>
                </div>
                <div class="rounded-2xl bg-[#f6f6f3] px-4 py-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white">
                        <svg class="h-5 w-5 text-[#E22625]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-[#000000]">Isi Deposit</p>
                    <p class="mt-0.5 text-xs text-[#62625b]">Top up saldo kapan saja.</p>
                </div>
                <div class="rounded-2xl bg-[#f6f6f3] px-4 py-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white">
                        <svg class="h-5 w-5 text-[#E22625]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-[#000000]">Notifikasi</p>
                    <p class="mt-0.5 text-xs text-[#62625b]">Update promo & pesanan.</p>
                </div>
            </div>
        </div>
    </div>
</template>
