<script setup lang="ts">
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

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
    <div class="space-y-4">
        <h2 class="text-xl font-semibold">Halo, Selamat Datang!</h2>

        <div class="grid grid-cols-1 gap-3">
            <Card class="bg-orange-50 dark:bg-orange-950">
                <CardContent class="flex items-center justify-between p-4">
                    <div>
                        <p class="text-sm text-muted-foreground">Total Poin</p>
                        <p class="text-2xl font-bold text-orange-600">{{ stats.total_points }}</p>
                    </div>
                    <div class="rounded-full bg-orange-100 p-3 dark:bg-orange-900">
                        <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-blue-50 dark:bg-blue-950">
                <CardContent class="flex items-center justify-between p-4">
                    <div>
                        <p class="text-sm text-muted-foreground">Saldo Deposit</p>
                        <p class="text-2xl font-bold text-blue-600">{{ formatRupiah(stats.deposit_balance) }}</p>
                    </div>
                    <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-green-50 dark:bg-green-950">
                <CardContent class="flex items-center justify-between p-4">
                    <div>
                        <p class="text-sm text-muted-foreground">Voucher Aktif</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats.active_vouchers }}</p>
                    </div>
                    <div class="rounded-full bg-green-100 p-3 dark:bg-green-900">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Reward terbaru -->
        <div>
            <h3 class="mb-3 font-semibold">Reward Terbaru</h3>
            <Card class="p-4 text-center text-muted-foreground">
                <p>Belum ada reward tersedia.</p>
            </Card>
        </div>
    </div>
</template>
