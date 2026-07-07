<script setup lang="ts">
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: MemberLayout });

const { vouchers } = defineProps<{
    vouchers: {
        data: Array<{
            id: number;
            voucher: {
                code: string;
                type: string;
                discount_type: string;
                discount_value: number;
                max_discount: number | null;
                valid_from: string | null;
                valid_until: string | null;
            };
            status: string;
            redeemed_at: string | null;
        }>;
    } | null;
}>();

const typeLabels: Record<string, string> = {
    birthday: 'Ulang Tahun',
    golden_hour: 'Golden Hour',
    manual: 'Promo',
};

const statusColors: Record<string, string> = {
    active: 'bg-green-100 text-green-700',
    used: 'bg-gray-100 text-gray-500',
    expired: 'bg-red-100 text-red-500',
};

const statusLabels: Record<string, string> = { active: 'Aktif', used: 'Terpakai', expired: 'Kadaluarsa' };
</script>

<template>
    <Head title="Voucher Saya" />

    <h2 class="font-semibold mb-4">Voucher Saya</h2>

    <div v-if="!vouchers || vouchers.data.length === 0" class="py-8 text-center text-muted-foreground">
        Belum ada voucher.
    </div>

    <div v-else class="space-y-3">
        <Card v-for="mv in vouchers.data" :key="mv.id">
            <CardContent class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-bold text-lg">{{ mv.voucher.code }}</p>
                        <p class="text-sm text-muted-foreground">{{ typeLabels[mv.voucher.type] ?? mv.voucher.type }}</p>
                        <p class="text-sm">
                            {{ mv.voucher.discount_type === 'percent' ? `Diskon ${mv.voucher.discount_value}%` : `Potongan Rp ${mv.voucher.discount_value.toLocaleString('id-ID')}` }}
                            <span v-if="mv.voucher.max_discount">(max Rp {{ mv.voucher.max_discount.toLocaleString('id-ID') }})</span>
                        </p>
                    </div>
                    <span :class="['rounded-full px-2.5 py-0.5 text-xs font-medium', statusColors[mv.status]]">
                        {{ statusLabels[mv.status] }}
                    </span>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
