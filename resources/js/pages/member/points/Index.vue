<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';

defineOptions({ layout: MemberLayout });

const { transactions, totalPoints } = defineProps<{
    transactions: {
        data: Array<{
            id: number;
            type: string;
            amount: number;
            note: string;
            created_at: string;
        }>;
    };
    totalPoints: number;
}>();

const typeLabels: Record<string, string> = {
    earn: 'Poin Masuk',
    redeem: 'Tukar Reward',
    expire: 'Kadaluarsa',
};

const typeIcons: Record<string, string> = {
    earn: '+',
    redeem: '-',
    expire: '-',
};

const typeColors: Record<string, string> = {
    earn: 'text-green-600',
    redeem: 'text-[#E22625]',
    expire: 'text-[#91918c]',
};
</script>

<template>
    <Head title="Riwayat Poin" />

    <div class="flex flex-col gap-4">
        <div>
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gift-icon lucide-gift text-[#E22625]"><path d="M12 7v14"/><path d="M20 11v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8"/><path d="M7.5 7a1 1 0 0 1 0-5A4.8 8 0 0 1 12 7a4.8 8 0 0 1 4.5-5 1 1 0 0 1 0 5"/><rect x="3" y="7" width="18" height="4" rx="1"/></svg>
                <h1 class="text-xl font-bold leading-[1.2] text-[#000000]">Riwayat Poin</h1>
            </div>
            <p class="mt-0.5 text-sm leading-[1.4] text-[#62625b]">Total poin kamu saat ini</p>
        </div>

        <!-- Total Points Card -->
        <div class="rounded-2xl border border-[#dadad3] bg-white p-5 text-center">
            <p class="text-sm leading-[1.4] text-[#91918c]">Total Poin</p>
            <p class="mt-1 text-[32px] font-bold leading-[1.1] text-[#E22625]">{{ totalPoints.toLocaleString('id-ID') }}</p>
        </div>

        <!-- Empty -->
        <div v-if="!transactions?.data?.length" class="py-8 text-center">
            <p class="text-sm text-[#91918c]">Belum ada riwayat poin.</p>
        </div>

        <!-- Transaction List -->
        <div v-else class="flex flex-col gap-2">
            <h2 class="text-sm font-semibold leading-[1.4] text-[#000000]">Riwayat Transaksi</h2>
            <div
                v-for="t in transactions.data"
                :key="t.id"
                class="flex items-center gap-3 rounded-xl border border-[#dadad3] bg-white p-3"
            >
                <div
                    :class="[
                        'flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-lg font-bold',
                        t.type === 'earn' ? 'bg-green-50 text-green-600' : 'bg-[#f6f6f3] text-[#91918c]',
                    ]"
                >
                    {{ typeIcons[t.type] ?? '?' }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold leading-[1.3] text-[#000000]">{{ typeLabels[t.type] ?? t.type }}</p>
                    <p class="text-xs leading-[1.4] text-[#91918c]">{{ t.note }}</p>
                    <p class="mt-0.5 text-[10px] text-[#91918c]">{{ new Date(t.created_at).toLocaleString('id-ID') }}</p>
                </div>
                <span
                    :class="[
                        'text-sm font-bold',
                        typeColors[t.type] ?? 'text-[#000000]',
                    ]"
                >
                    {{ typeIcons[t.type] === '+' ? '+' : '-' }}{{ t.amount }}
                </span>
            </div>
        </div>
    </div>
</template>
