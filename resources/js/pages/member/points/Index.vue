<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
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

const showInfo = ref(false);

const typeConfig: Record<string, { label: string; icon: string; color: string; bg: string }> = {
    earn: { label: 'Poin Masuk', icon: '+', color: 'text-green-600', bg: 'bg-green-50' },
    redeem: { label: 'Tukar Reward', icon: '-', color: 'text-[#E22625]', bg: 'bg-[#E22625]/10' },
    expire: { label: 'Kadaluarsa', icon: '-', color: 'text-[#91918c]', bg: 'bg-[#f6f6f3]' },
};
</script>

<template>
    <Head title="Poin" />

    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-[22px] font-bold leading-[1.2] tracking-tight text-[#000000]">
                    Poin
                </h1>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="showInfo = true"
                    class="inline-flex h-9 items-center gap-1 rounded-full bg-[#E22625] px-4 text-sm font-bold text-white transition-opacity hover:opacity-90"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M12 5v14m-7-7h14"/></svg>
                    Poin
                </button>
                <Link
                    :href="route('member.rewards')"
                    class="inline-flex h-9 items-center gap-1 rounded-full border border-[#dadad3] bg-white px-4 text-sm font-semibold text-[#62625b] transition-colors hover:border-[#000000] hover:text-[#000000]"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 7v14"/><path d="M20 11v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8"/><path d="M7.5 7a1 1 0 0 1 0-5A4.8 8 0 0 1 12 7a4.8 8 0 0 1 4.5-5 1 1 0 0 1 0 5"/><rect x="3" y="7" width="18" height="4" rx="1"/></svg>
                    Reward
                </Link>
            </div>
        </div>

        <!-- Total Points Card -->
        <div class="rounded-2xl bg-[#f6f6f3] px-5 py-5">
            <p class="text-xs font-semibold leading-[1.4] text-[#62625b]">Total Poin</p>
            <p class="mt-1 text-[32px] font-bold leading-[1.1] tracking-tight text-[#E22625]">
                {{ totalPoints.toLocaleString('id-ID') }}
            </p>
            <p class="mt-1 text-xs leading-[1.4] text-[#91918c]">
                Terkumpul dari setiap transaksi belanja
            </p>
        </div>

        <!-- Empty State -->
        <div
            v-if="!transactions?.data?.length"
            class="flex flex-col items-center py-16"
        >
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[#f6f6f3]">
                <svg class="h-8 w-8 text-[#c8c8c1]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M12 7v14"/><path d="M20 11v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8"/><path d="M7.5 7a1 1 0 0 1 0-5A4.8 8 0 0 1 12 7a4.8 8 0 0 1 4.5-5 1 1 0 0 1 0 5"/><rect x="3" y="7" width="18" height="4" rx="1"/></svg>
            </div>
            <p class="mt-4 text-sm font-semibold text-[#000000]">Belum ada poin</p>
            <p class="mt-1 text-xs text-[#91918c]">Mulai belanja dan kumpulkan poin dari setiap transaksi</p>
        </div>

        <!-- Transaction List -->
        <div v-else class="flex flex-col gap-3">
            <div>
                <h2 class="text-sm font-bold leading-[1.3] text-[#000000]">Riwayat Poin</h2>
            </div>
            <div
                v-for="t in transactions.data"
                :key="t.id"
                class="flex items-center gap-3 rounded-2xl border border-[#dadad3] bg-white px-4 py-3"
            >
                <div
                    :class="[
                        'flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-lg font-bold',
                        typeConfig[t.type]?.bg ?? 'bg-[#f6f6f3]',
                        typeConfig[t.type]?.color ?? 'text-[#91918c]',
                    ]"
                >
                    {{ typeConfig[t.type]?.icon ?? '?' }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold leading-[1.3] text-[#000000]">{{ typeConfig[t.type]?.label ?? t.type }}</p>
                    <p class="text-xs leading-[1.4] text-[#62625b]">{{ t.note }}</p>
                    <p class="mt-0.5 text-[10px] leading-[1.4] text-[#91918c]">
                        {{ new Date(t.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                    </p>
                </div>
                <span
                    :class="[
                        'shrink-0 text-sm font-bold',
                        typeConfig[t.type]?.color ?? 'text-[#000000]',
                    ]"
                >
                    {{ typeConfig[t.type]?.icon ?? '' }}{{ t.amount }}
                </span>
            </div>
        </div>

        <!-- Info Modal -->
        <Teleport to="body">
            <div
                v-if="showInfo"
                class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 sm:items-center"
                @click.self="showInfo = false"
            >
                <div class="w-full max-w-md rounded-t-2xl bg-white p-6 shadow-xl sm:rounded-2xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-[#000000]">Tentang Poin</h3>
                        <button
                            @click="showInfo = false"
                            class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-[#f6f6f3]"
                        >
                            <svg class="h-4 w-4 text-[#62625b]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="mt-5 flex flex-col gap-4">
                        <!-- How to earn -->
                        <div class="rounded-2xl border border-[#dadad3] bg-[#fbfbf9] p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white">
                                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 7v14"/><path d="M20 11v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8"/><path d="M7.5 7a1 1 0 0 1 0-5A4.8 8 0 0 1 12 7a4.8 8 0 0 1 4.5-5 1 1 0 0 1 0 5"/><rect x="3" y="7" width="18" height="4" rx="1"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-[#000000]">Dapatkan Poin</p>
                                    <p class="mt-0.5 text-xs leading-[1.5] text-[#62625b]">
                                        Poin didapatkan secara otomatis setiap kali kamu berbelanja di {{ $page.props.branding?.app_name ?? 'WarungMember' }}.
                                        Semakin sering belanja, semakin banyak poin yang terkumpul.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- How to use -->
                        <div class="rounded-2xl border border-[#dadad3] bg-[#fbfbf9] p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white">
                                    <svg class="h-5 w-5 text-[#E22625]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-[#000000]">Tukarkan Poin</p>
                                    <p class="mt-0.5 text-xs leading-[1.5] text-[#62625b]">
                                        Kumpulkan poin dan tukarkan dengan berbagai reward menarik yang tersedia.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="rounded-xl bg-[#f6f6f3] px-4 py-3">
                            <p class="text-xs leading-[1.5] text-[#62625b]">
                                Poin memiliki masa berlaku. Pastikan kamu menukarkan poin sebelum kadaluarsa.
                            </p>
                        </div>
                    </div>

                    <p class="mt-5 text-center text-xs leading-[1.5] text-[#91918c]">
                        Poin akan otomatis bertambah setiap transaksi berstatus Selesai.
                    </p>
                </div>
            </div>
        </Teleport>
    </div>
</template>
