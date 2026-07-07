<script setup lang="ts">
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Link } from '@inertiajs/vue3';

defineOptions({
    layout: MemberLayout,
});

const { stats } = defineProps<{
    stats: {
        total_points: number;
        deposit_balance: number;
        active_vouchers: number;
    };
}>();

function formatRupiah(n: number): string {
    return 'Rp ' + n.toLocaleString('id-ID');
}
</script>

<template>
    <div class="flex flex-col gap-6">
        <!-- Welcome -->
        <div>
            <h2 class="text-[22px] font-semibold leading-[1.25] text-[#000000]">
                Halo, Selamat Datang!
            </h2>
            <p class="mt-1 text-sm leading-[1.4] text-[#62625b]">
                Lihat poin, saldo, dan voucher kamu di sini.
            </p>
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

            <!-- Voucher -->
            <div class="flex items-center justify-between rounded-2xl bg-[#f6f6f3] px-4 py-4">
                <div>
                    <p class="text-sm leading-[1.4] text-[#62625b]">Voucher Aktif</p>
                    <p class="mt-0.5 text-[22px] font-semibold leading-[1.25] text-[#000000]">
                        {{ stats.active_vouchers }}
                    </p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white">
                    <svg class="h-5 w-5 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div>
            <h3 class="text-lg font-semibold leading-[1.3] text-[#000000]">Aksi Cepat</h3>
            <div class="mt-3 flex flex-wrap gap-2">
                <Link
                    :href="route('member.points')"
                    class="inline-flex rounded-full bg-[#f6f6f3] px-4 py-2 text-sm font-semibold leading-[1.4] text-[#000000] transition-colors hover:bg-[#000000] hover:text-white"
                >
                    Riwayat Poin
                </Link>
                <Link
                    :href="route('member.rewards')"
                    class="inline-flex rounded-full bg-[#f6f6f3] px-4 py-2 text-sm font-semibold leading-[1.4] text-[#000000] transition-colors hover:bg-[#000000] hover:text-white"
                >
                    Tukar Reward
                </Link>
                <Link
                    :href="route('member.vouchers')"
                    class="inline-flex rounded-full bg-[#f6f6f3] px-4 py-2 text-sm font-semibold leading-[1.4] text-[#000000] transition-colors hover:bg-[#000000] hover:text-white"
                >
                    Voucher Saya
                </Link>
            </div>
        </div>

        <!-- Reward Terbaru -->
        <div>
            <h3 class="text-lg font-semibold leading-[1.3] text-[#000000]">Reward Terbaru</h3>
            <div class="mt-3 rounded-2xl bg-[#f6f6f3] px-4 py-6 text-center">
                <p class="text-sm leading-[1.4] text-[#91918c]">Belum ada reward tersedia.</p>
            </div>
        </div>
    </div>
</template>
