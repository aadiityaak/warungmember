<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Deposit', href: route('admin.deposits.index') },
            { title: 'Riwayat' },
        ] as BreadcrumbItem[],
    },
});

const { member, transactions } = defineProps<{
    member: { id: number; user: { name: string; email: string } };
    transactions: {
        data: Array<{
            id: number;
            type: string;
            amount: number;
            note: string | null;
            created_at: string;
            reference: { id: number; name: string } | null;
        }>;
    };
}>();

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head :title="`Riwayat Deposit — ${member.user.name}`" />

    <div class="mx-6 pt-6">
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Riwayat Deposit
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                {{ member.user.name }} — {{ member.user.email }}
            </p>
        </header>

        <div v-if="transactions.data.length === 0" class="rounded-2xl bg-[#f6f6f3] py-16 text-center">
            <p class="text-sm leading-[1.4] text-[#62625b]">Belum ada transaksi deposit.</p>
        </div>

        <div v-else class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#dadad3]">
                        <th class="px-5 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Tanggal</th>
                        <th class="px-5 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Tipe</th>
                        <th class="px-5 py-3 text-right text-sm font-bold leading-[1.4] text-[#000000]">Jumlah</th>
                        <th class="px-5 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden sm:table-cell">Diinput oleh</th>
                        <th class="px-5 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden md:table-cell">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="t in transactions.data"
                        :key="t.id"
                        class="border-b border-[#e5e5e0] last:border-0 transition-colors hover:bg-[#fbfbf9]"
                    >
                        <td class="px-5 py-3 text-sm leading-[1.4] text-[#62625b] whitespace-nowrap">
                            {{ formatDate(t.created_at) }}
                        </td>
                        <td class="px-5 py-3">
                            <span
                                class="inline-block rounded-full px-3 py-0.5 text-xs font-bold leading-[1.4]"
                                :class="t.type === 'topup' ? 'bg-[#e8f5e9] text-[#2e7d32]' : 'bg-[#fef2f2] text-[#991b1b]'"
                            >
                                {{ t.type === 'topup' ? 'Top-up' : t.type }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-sm leading-[1.4] font-semibold text-right text-[#000000]">
                            Rp {{ t.amount.toLocaleString('id-ID') }}
                        </td>
                        <td class="px-5 py-3 text-sm leading-[1.4] text-[#62625b] hidden sm:table-cell">
                            {{ t.reference?.name ?? '-' }}
                        </td>
                        <td class="px-5 py-3 text-sm leading-[1.4] text-[#91918c] hidden md:table-cell">
                            {{ t.note ?? '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <Link :href="route('admin.deposits.index')" class="text-sm font-semibold text-[#62625b] hover:text-[#000000] transition-colors">
                &larr; Kembali
            </Link>
        </div>
    </div>
</template>
