<script setup lang="ts">
import { ref } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Head, router } from '@inertiajs/vue3';

defineOptions({ layout: MemberLayout });

const props = defineProps<{
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
    availableVouchers: Array<{
        id: number;
        code: string;
        type: string;
        discount_type: string;
        discount_value: number;
        max_discount: number | null;
        min_purchase: number;
        points_required: number | null;
        valid_from: string | null;
        valid_until: string | null;
    }>;
    memberPoints: number;
    claimedVoucherIds: number[];
}>();

const tab = ref<'mine' | 'available'>('mine');

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

function claim(voucherId: number) {
    router.post(route('member.vouchers.claim', { voucher: voucherId }), {}, {
        preserveScroll: true,
    });
}

function formatDiscount(v: { discount_type: string; discount_value: number; max_discount: number | null }): string {
    if (v.discount_type === 'percent') {
        const s = `Diskon ${v.discount_value}%`;
        return v.max_discount ? `${s} (max Rp ${v.max_discount.toLocaleString('id-ID')})` : s;
    }
    return `Potongan Rp ${v.discount_value.toLocaleString('id-ID')}`;
}
</script>

<template>
    <Head title="Voucher" />

    <!-- Tabs -->
    <div class="mb-4 flex gap-1 rounded-lg bg-[#f0f0ec] p-1">
        <button
            class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
            :class="tab === 'mine' ? 'bg-white text-black shadow-sm' : 'text-[#62625b]'"
            @click="tab = 'mine'"
        >
            Voucher Saya
        </button>
        <button
            class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
            :class="tab === 'available' ? 'bg-white text-black shadow-sm' : 'text-[#62625b]'"
            @click="tab = 'available'"
        >
            Tersedia
        </button>
    </div>

    <!-- Voucher Saya -->
    <div v-if="tab === 'mine'">
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
                            <p class="text-sm">{{ formatDiscount(mv.voucher) }}</p>
                        </div>
                        <span :class="['rounded-full px-2.5 py-0.5 text-xs font-medium', statusColors[mv.status]]">
                            {{ statusLabels[mv.status] }}
                        </span>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>

    <!-- Tersedia -->
    <div v-else>
        <div v-if="availableVouchers.length === 0" class="py-8 text-center text-muted-foreground">
            Tidak ada voucher tersedia saat ini.
        </div>
        <div v-else class="space-y-3">
            <Card v-for="v in availableVouchers" :key="v.id">
                <CardContent class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-lg">{{ v.code }}</p>
                            <p class="text-xs text-muted-foreground">{{ typeLabels[v.type] ?? v.type }}</p>
                            <p class="text-sm">{{ formatDiscount(v) }}</p>
                            <p v-if="v.min_purchase" class="text-xs text-muted-foreground">Min. belanja Rp {{ v.min_purchase.toLocaleString('id-ID') }}</p>
                            <p v-if="v.valid_until" class="text-xs text-muted-foreground">Berlaku hingga {{ new Date(v.valid_until).toLocaleDateString('id-ID') }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p v-if="v.points_required" class="mb-1 text-sm font-semibold text-[#E22625]">{{ v.points_required.toLocaleString('id-ID') }} poin</p>
                            <p v-else class="mb-1 text-sm font-semibold text-green-600">Gratis</p>
                            <Button
                                v-if="!claimedVoucherIds.includes(v.id)"
                                size="sm"
                                :disabled="!!v.points_required && memberPoints < v.points_required"
                                @click="claim(v.id)"
                            >
                                Klaim
                            </Button>
                            <span v-else class="text-xs text-muted-foreground">Sudah dimiliki</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
