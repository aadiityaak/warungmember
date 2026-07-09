<script setup lang="ts">
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

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

function formatPoints(n: number): string {
    return n.toLocaleString('id-ID');
}

const tierLabel = computed(() => {
    if (memberPoints >= 1000) return 'Rewards Lover';
    if (memberPoints >= 500) return 'Member Aktif';
    if (memberPoints >= 100) return 'Member Baru';
    return 'Kolektor Poin';
});

const tierIcon = computed(() => {
    if (memberPoints >= 1000) return '👑';
    if (memberPoints >= 500) return '⭐';
    if (memberPoints >= 100) return '🌱';
    return '💎';
});
</script>

<template>
    <Head title="Reward" />

    <div class="flex flex-col gap-4">
        <!-- Points Header -->
        <div class="rounded-2xl bg-[#f6f6f3] p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm leading-[1.4] text-[#62625b]">Poin Kamu</p>
                    <p class="mt-1 text-[32px] font-bold leading-[1.1] text-[#000000]">
                        {{ formatPoints(memberPoints) }}
                    </p>
                    <p class="mt-0.5 text-xs leading-[1.4] text-[#91918c]">
                        {{ tierIcon }} {{ tierLabel }}
                    </p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white">
                    <svg class="h-7 w-7 text-[#e60023]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Section Title -->
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold leading-[1.3] text-[#000000]">Katalog Reward</h2>
            <span v-if="rewards.length" class="text-xs font-semibold leading-[1.4] text-[#91918c]">
                {{ rewards.length }} reward
            </span>
        </div>

        <!-- Empty State -->
        <div v-if="rewards.length === 0" class="flex flex-col items-center gap-3 py-12">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[#f6f6f3]">
                <svg class="h-7 w-7 text-[#91918c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 7v14" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 11v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.5 7a1 1 0 0 1 0-5A4.8 8 0 0 1 12 7a4.8 8 0 0 1 4.5-5 1 1 0 0 1 0 5" />
                    <rect x="3" y="7" width="18" height="4" rx="1" />
                </svg>
            </div>
            <p class="text-sm leading-[1.4] text-[#91918c]">Belum ada reward tersedia.</p>
        </div>

        <!-- Reward List -->
        <div v-else class="flex flex-col gap-3">
            <div
                v-for="reward in rewards"
                :key="reward.id"
                class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white"
            >
                <div class="flex items-center gap-4 p-4">
                    <!-- Reward Image -->
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-[#f6f6f3]">
                        <img
                            v-if="reward.image"
                            :src="reward.image"
                            :alt="reward.name"
                            class="h-full w-full object-cover"
                        />
                        <svg v-else class="h-7 w-7 text-[#c8c8c1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 7v14" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 11v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.5 7a1 1 0 0 1 0-5A4.8 8 0 0 1 12 7a4.8 8 0 0 1 4.5-5 1 1 0 0 1 0 5" />
                        </svg>
                    </div>

                    <!-- Reward Info -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold leading-[1.3] text-[#000000] truncate">
                            {{ reward.name }}
                        </p>
                        <p v-if="reward.description" class="mt-0.5 text-xs leading-[1.4] text-[#62625b] line-clamp-2">
                            {{ reward.description }}
                        </p>
                        <div class="mt-1.5 flex items-center gap-2">
                            <span class="text-sm font-bold leading-[1.4] text-[#e60023]">
                                {{ formatPoints(reward.points_required) }} poin
                            </span>
                            <span
                                v-if="reward.stock !== null"
                                class="text-[10px] leading-[1.4] text-[#91918c]"
                            >
                                Sisa {{ reward.stock }}
                            </span>
                        </div>
                    </div>

                    <!-- Redeem Button -->
                    <button
                        :disabled="memberPoints < reward.points_required || (reward.stock !== null && reward.stock <= 0) || form.processing"
                        class="inline-flex shrink-0 items-center justify-center gap-1 rounded-2xl px-3.5 py-1.5 text-sm font-bold leading-none transition-all disabled:pointer-events-none disabled:opacity-40"
                        :class="memberPoints >= reward.points_required && (reward.stock === null || reward.stock > 0)
                            ? 'bg-[#e60023] text-white hover:bg-[#cc001f]'
                            : 'bg-[#f6f6f3] text-[#91918c]'"
                        @click="redeem(reward.id)"
                    >
                        <svg v-if="form.processing && form.recentlySuccessful" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else-if="form.processing" class="h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span v-else>Tukar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
