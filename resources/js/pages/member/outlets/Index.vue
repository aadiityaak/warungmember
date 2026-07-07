<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';

defineOptions({ layout: MemberLayout });

const { outlets } = defineProps<{
    outlets: Array<{
        id: number;
        name: string;
        address: string | null;
        phone: string | null;
    }>;
}>();
</script>

<template>
    <Head title="Lokasi Outlet" />

    <div class="flex flex-col gap-4">
        <div>
            <h1 class="text-xl font-bold leading-[1.2] text-[#000000]">Lokasi Outlet</h1>
            <p class="mt-0.5 text-sm leading-[1.4] text-[#62625b]">Temukan Warung Mas Mbull terdekat</p>
        </div>

        <div v-if="outlets.length === 0" class="py-12 text-center">
            <p class="text-sm text-[#91918c]">Belum ada outlet tersedia.</p>
        </div>

        <div v-else class="flex flex-col gap-3">
            <div
                v-for="outlet in outlets"
                :key="outlet.id"
                class="rounded-2xl border border-[#dadad3] bg-white p-4"
            >
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#f6f6f3]">
                        <svg class="h-5 w-5 text-[#E22625]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold leading-[1.3] text-[#000000]">{{ outlet.name }}</h3>
                        <p class="mt-1 text-sm leading-[1.4] text-[#62625b]">{{ outlet.address ?? '-' }}</p>
                        <p class="mt-0.5 text-sm leading-[1.4] text-[#91918c]">{{ outlet.phone ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
