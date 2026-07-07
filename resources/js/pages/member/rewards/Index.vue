<script setup lang="ts">
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Head, useForm } from '@inertiajs/vue3';

defineOptions({ layout: MemberLayout });

const { rewards, memberPoints } = defineProps<{
    rewards: Array<{
        id: number;
        name: string;
        description: string | null;
        image: string | null;
        points_required: number;
        stock: number | null;
        is_active: boolean;
    }>;
    memberPoints: number;
}>();

const form = useForm({});

function redeem(rewardId: number) {
    form.post(route('member.rewards.redeem', rewardId));
}
</script>

<template>
    <Head title="Reward" />

    <div class="space-y-4">
        <div class="rounded-lg bg-orange-50 p-4 text-center">
            <p class="text-sm text-muted-foreground">Poin Kamu</p>
            <p class="text-3xl font-bold text-orange-600">{{ memberPoints }}</p>
        </div>

        <h2 class="font-semibold">Katalog Reward</h2>

        <div v-if="rewards.length === 0" class="py-8 text-center text-muted-foreground">
            Belum ada reward tersedia.
        </div>

        <div v-else class="space-y-3">
            <Card v-for="reward in rewards" :key="reward.id">
                <CardContent class="flex items-center gap-3 p-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-2xl">
                        🎁
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium truncate">{{ reward.name }}</p>
                        <p class="text-sm text-muted-foreground">{{ reward.description }}</p>
                        <p class="text-sm font-semibold text-orange-600">{{ reward.points_required }} poin</p>
                        <p v-if="reward.stock !== null" class="text-xs text-muted-foreground">Stok: {{ reward.stock }}</p>
                    </div>
                    <Button
                        size="sm"
                        :disabled="memberPoints < reward.points_required || (reward.stock !== null && reward.stock <= 0) || form.processing"
                        @click="redeem(reward.id)"
                    >
                        Tukar
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
