<script setup lang="ts">
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: MemberLayout });

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

function formatRupiah(n: number): string {
    return 'Rp ' + n.toLocaleString('id-ID');
}

const typeMap: Record<string, { label: string; color: string }> = {
    topup: { label: 'Top-up', color: 'text-green-600' },
    payment: { label: 'Pembayaran', color: 'text-[#E22625]' },
    refund: { label: 'Refund', color: 'text-blue-600' },
};
</script>

<template>
    <Head title="Deposit" />

    <div class="space-y-4">
        <div class="rounded-lg bg-blue-50 p-4 text-center">
            <p class="text-sm text-muted-foreground">Saldo Deposit</p>
            <p class="text-3xl font-bold text-blue-600">{{ formatRupiah(balance) }}</p>
        </div>

        <h2 class="font-semibold">Riwayat Deposit</h2>

        <div v-if="!transactions || transactions.data.length === 0" class="py-8 text-center text-muted-foreground">
            Belum ada riwayat deposit.
        </div>

        <div v-else class="space-y-2">
            <Card v-for="tx in transactions.data" :key="tx.id">
                <CardContent class="flex items-center justify-between p-3">
                    <div>
                        <p class="text-sm">{{ tx.note ?? typeMap[tx.type]?.label }}</p>
                        <p class="text-xs text-muted-foreground">{{ new Date(tx.created_at).toLocaleDateString('id-ID') }}</p>
                    </div>
                    <span :class="['font-semibold', tx.type === 'topup' || tx.type === 'refund' ? 'text-green-600' : 'text-red-600']">
                        {{ tx.type === 'topup' || tx.type === 'refund' ? '+' : '-' }}{{ formatRupiah(tx.amount) }}
                    </span>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
