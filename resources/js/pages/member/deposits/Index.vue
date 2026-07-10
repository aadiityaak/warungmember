<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';

defineOptions({ layout: MemberLayout });

const page = usePage();

const { transactions, balance } = defineProps<{
    transactions: {
        data: Array<{
            id: number;
            type: string;
            amount: number;
            note: string | null;
            created_at: string;
        }>;
    } | null;
    balance: number;
}>();

const showDepositInfo = ref(false);

const whatsappNumber = (page.props.branding as Record<string, any>)?.whatsapp_number ?? '6281335405231';
const waUrl = 'https://wa.me/' + whatsappNumber.replace(/^0/, '62') + '?text=Halo%20saya%20ingin%20top-up%20saldo%20deposit';

function formatRupiah(n: number): string {
    return 'Rp' + n.toLocaleString('id-ID');
}

const typeConfig: Record<string, { label: string; sign: string; color: string }> = {
    topup: { label: 'Top-up', sign: '+', color: 'text-green-600' },
    payment: { label: 'Pembayaran Pesanan', sign: '-', color: 'text-[#E22625]' },
    refund: { label: 'Refund', sign: '+', color: 'text-blue-600' },
};
</script>

<template>
    <Head title="Deposit" />

    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-[22px] font-bold leading-[1.2] tracking-tight text-[#000000]">
                    Deposit
                </h1>
                <p class="mt-1 text-sm leading-[1.4] text-[#62625b]">
                    Kelola saldo deposit kamu
                </p>
            </div>
            <button
                @click="showDepositInfo = true"
                class="inline-flex h-9 items-center gap-1 rounded-full bg-[#E22625] px-4 text-sm font-bold text-white transition-opacity hover:opacity-90"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M12 5v14m-7-7h14"/></svg>
                Deposit
            </button>
        </div>

        <!-- Balance Card -->
        <div class="rounded-2xl bg-[#f6f6f3] px-5 py-5">
            <p class="text-xs font-semibold leading-[1.4] text-[#62625b]">Saldo Deposit</p>
            <p class="mt-1 text-[28px] font-bold leading-[1.2] tracking-tight text-[#000000]">
                {{ formatRupiah(balance) }}
            </p>
            <p class="mt-1 text-xs leading-[1.4] text-[#91918c]">
                Siap digunakan untuk pembayaran pesanan
            </p>
        </div>

        <!-- History Title -->
        <div>
            <h2 class="text-sm font-bold leading-[1.3] text-[#000000]">Riwayat Deposit</h2>
        </div>

        <!-- Empty State -->
        <div
            v-if="!transactions || transactions.data.length === 0"
            class="flex flex-col items-center py-16"
        >
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[#f6f6f3]">
                <svg class="h-8 w-8 text-[#c8c8c1]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </div>
            <p class="mt-4 text-sm font-semibold text-[#000000]">Belum ada riwayat</p>
            <p class="mt-1 text-xs text-[#91918c]">Setiap transaksi deposit akan muncul di sini</p>
        </div>

        <!-- Transaction List -->
        <div v-else class="flex flex-col gap-2">
            <div
                v-for="tx in transactions.data"
                :key="tx.id"
                class="rounded-2xl border border-[#dadad3] bg-white px-4 py-3"
            >
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold leading-[1.4] text-[#000000] truncate">
                            {{ tx.note ?? typeConfig[tx.type]?.label ?? tx.type }}
                        </p>
                        <div class="mt-0.5 flex items-center gap-2">
                            <span class="text-xs text-[#91918c]">
                                {{ new Date(tx.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                            </span>
                            <span
                                class="text-[10px] font-semibold uppercase tracking-wider"
                                :class="typeConfig[tx.type]?.color ?? 'text-[#91918c]'"
                            >
                                {{ typeConfig[tx.type]?.label ?? tx.type }}
                            </span>
                        </div>
                    </div>
                    <span
                        class="shrink-0 text-sm font-bold"
                        :class="tx.type === 'topup' || tx.type === 'refund' ? 'text-green-600' : 'text-[#E22625]'"
                    >
                        {{ tx.type === 'topup' || tx.type === 'refund' ? '+' : '-' }}{{ formatRupiah(tx.amount) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Deposit Info Modal -->
        <Teleport to="body">
            <div
                v-if="showDepositInfo"
                class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 sm:items-center"
                @click.self="showDepositInfo = false"
            >
                <div class="w-full max-w-md rounded-t-2xl bg-white p-6 shadow-xl sm:rounded-2xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-[#000000]">Cara Isi Deposit</h3>
                        <button
                            @click="showDepositInfo = false"
                            class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-[#f6f6f3]"
                        >
                            <svg class="h-4 w-4 text-[#62625b]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="mt-5 flex flex-col gap-4">
                        <!-- Option 1: At Outlet -->
                        <div class="rounded-2xl border border-[#dadad3] bg-[#fbfbf9] p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white">
                                    <svg class="h-5 w-5 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M15 21v-5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v5"/><path d="M17.774 10.31a1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.451 0 1.12 1.12 0 0 0-1.548 0 2.5 2.5 0 0 1-3.452 0 1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.77-3.248l2.889-4.184A2 2 0 0 1 7 2h10a2 2 0 0 1 1.653.873l2.895 4.192a2.5 2.5 0 0 1-3.774 3.244"/><path d="M4 10.95V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8.05"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-[#000000]">Datang ke Outlet</p>
                                    <p class="mt-0.5 text-xs leading-[1.5] text-[#62625b]">
                                        Kunjungi cabang {{ $page.props.branding?.app_name ?? 'WarungMember' }} terdekat dan lakukan pembayaran ke kasir.
                                    </p>
                                </div>
                            </div>
                            <Link
                                :href="route('member.outlets.index')"
                                class="mt-3 inline-flex h-8 items-center rounded-full bg-[#000000] px-4 text-xs font-bold text-white transition-opacity hover:opacity-90"
                            >
                                Cari Outlet Terdekat
                            </Link>
                        </div>

                        <!-- Option 2: Via WhatsApp -->
                        <div class="rounded-2xl border border-[#dadad3] bg-[#fbfbf9] p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white">
                                    <svg class="h-5 w-5 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-[#000000]">Hubungi WhatsApp CS</p>
                                    <p class="mt-0.5 text-xs leading-[1.5] text-[#62625b]">
                                        Tanyakan informasi deposit dan cara pengisian melalui Customer Service kami.
                                    </p>
                                </div>
                            </div>
                            <a
                                :href="waUrl"
                                target="_blank"
                                class="mt-3 inline-flex h-8 items-center rounded-full bg-[#25D366] px-4 text-xs font-bold text-white transition-opacity hover:opacity-90"
                            >
                                <svg class="mr-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                Hubungi CS via WhatsApp
                            </a>
                        </div>
                    </div>

                    <p class="mt-5 text-center text-xs leading-[1.5] text-[#91918c]">
                        Setelah pembayaran dikonfirmasi, saldo akan langsung bertambah dan bisa digunakan untuk bertransaksi.
                    </p>
                </div>
            </div>
        </Teleport>
    </div>
</template>
