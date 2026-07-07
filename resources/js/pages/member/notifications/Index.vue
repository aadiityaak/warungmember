<script setup lang="ts">
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: MemberLayout });

const { notifications } = defineProps<{
    notifications: {
        data: Array<{
            id: number;
            type: string;
            title: string;
            body: string;
            read_at: string | null;
            created_at: string;
        }>;
    };
}>();

const typeIcons: Record<string, string> = {
    promo: '📢',
    voucher: '🎫',
    poin: '⭐',
    deposit: '💰',
    umum: '📌',
};

const typeColors: Record<string, string> = {
    promo: 'border-l-orange-500 bg-orange-50',
    voucher: 'border-l-green-500 bg-green-50',
    poin: 'border-l-yellow-500 bg-yellow-50',
    deposit: 'border-l-blue-500 bg-blue-50',
    umum: 'border-l-gray-500 bg-gray-50',
};

const typeLabels: Record<string, string> = {
    promo: 'Promo',
    voucher: 'Voucher',
    poin: 'Poin',
    deposit: 'Deposit',
    umum: 'Umum',
};
</script>

<template>
    <Head title="Notifikasi" />

    <h2 class="font-semibold mb-4">Notifikasi</h2>

    <div v-if="!notifications || notifications.data.length === 0" class="py-8 text-center text-muted-foreground">
        Belum ada notifikasi.
    </div>

    <div v-else class="space-y-2">
        <Card
            v-for="n in notifications.data"
            :key="n.id"
            :class="['border-l-4', typeColors[n.type] ?? typeColors.umum, !n.read_at ? 'ring-1 ring-orange-200' : 'opacity-70']"
        >
            <CardContent class="flex gap-3 p-3">
                <span class="text-lg">{{ typeIcons[n.type] ?? '📌' }}</span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <Badge variant="secondary" class="text-[10px]">{{ typeLabels[n.type] ?? n.type }}</Badge>
                        <span v-if="!n.read_at" class="h-2 w-2 rounded-full bg-orange-500" />
                    </div>
                    <p class="font-medium text-sm mt-0.5">{{ n.title }}</p>
                    <p class="text-xs text-muted-foreground">{{ n.body }}</p>
                    <p class="text-[10px] text-muted-foreground mt-1">{{ new Date(n.created_at).toLocaleString('id-ID') }}</p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
