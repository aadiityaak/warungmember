<script setup lang="ts">
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: MemberLayout });

const { transactions, totalPoints } = defineProps<{
    transactions: {
        data: Array<{
            id: number;
            type: string;
            amount: number;
            note: string | null;
            created_at: string;
        }>;
    } | null;
    totalPoints: number;
}>();

const typeMap: Record<string, { label: string; color: string }> = {
    earn: { label: 'Bertambah', color: 'text-green-600' },
    redeem: { label: 'Ditukar', color: 'text-orange-600' },
    expire: { label: 'Kadaluarsa', color: 'text-red-600' },
};
</script>

<template>
    <Head title="Riwayat Poin" />

    <div class="space-y-4">
        <div class="rounded-lg bg-orange-50 p-4 text-center dark:bg-orange-950">
            <p class="text-sm text-muted-foreground">Total Poin</p>
            <p class="text-3xl font-bold text-orange-600">{{ totalPoints }}</p>
        </div>

        <h2 class="font-semibold">Riwayat Poin</h2>

        <div v-if="!transactions || transactions.data.length === 0" class="py-8 text-center text-muted-foreground">
            Belum ada riwayat poin.
        </div>

        <div v-else class="space-y-2">
            <Card v-for="tx in transactions.data" :key="tx.id">
                <CardContent class="flex items-center justify-between p-3">
                    <div>
                        <p class="text-sm">{{ tx.note ?? typeMap[tx.type]?.label }}</p>
                        <p class="text-xs text-muted-foreground">{{ new Date(tx.created_at).toLocaleDateString('id-ID') }}</p>
                    </div>
                    <span :class="['font-semibold', tx.type === 'earn' ? 'text-green-600' : 'text-red-600']">
                        {{ tx.type === 'earn' ? '+' : '-' }}{{ tx.amount }}
                    </span>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
