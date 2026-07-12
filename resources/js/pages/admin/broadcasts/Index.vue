<script setup lang="ts">
import { computed, Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes/admin';
import { create as createBroadcast } from '@/routes/admin/broadcasts';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Broadcast' },
        ] as BreadcrumbItem[],
    },
});

const { broadcasts: broadcastsData } = defineProps<{
    broadcasts: {
        data: Array<{
            id: number;
            type: string;
            title: string;
            body: string;
            sent_count: number;
            delivery_log: null | {
                total_push_attempts: number;
                ntfy_success: number;
                ntfy_failed: number;
            };
            read_count: number;
            sent_at: string | null;
            created_at: string;
        }>;
    };
}>();

function resend(broadcastId: number) {
    router.post(route('admin.broadcasts.resend', broadcastId), {}, {
        preserveScroll: true,
    });
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head title="Broadcast" />

    <div class="flex flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-[#000000]">Broadcast</h2>
            <Link :href="createBroadcast()" class="inline-flex h-9 items-center rounded-full bg-[#E22625] px-5 text-sm font-bold text-white hover:opacity-90 transition-all">Buat Broadcast</Link>
        </div>

        <div class="rounded-2xl border border-[#dadad3] bg-white overflow-hidden">
            <div v-if="broadcasts.data.length === 0" class="p-6 text-center text-sm text-[#91918c]">Belum ada broadcast.</div>
            <div v-else class="divide-y divide-[#dadad3]">
                <div v-for="b in broadcasts.data" :key="b.id" class="px-5 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-[#000000]">{{ b.title }}</p>
                            <p class="mt-1 text-xs text-[#91918c] line-clamp-2">{{ b.body }}</p>
                            <div class="mt-2 flex items-center gap-3 text-xs text-[#91918c]">
                                <span class="inline-flex items-center gap-1 rounded-full bg-[#f6f6f3] px-2 py-0.5 font-medium">{{ b.type === 'email' ? 'Email' : 'Notifikasi' }}</span>
                                <span>{{ b.sent_count }} member</span>
                                <span v-if="b.read_count > 0" class="text-emerald-600">{{ b.read_count }} dibaca</span>
                                <span>{{ formatDate(b.sent_at ?? b.created_at) }}</span>
                            </div>
                            <!-- Delivery Stats -->
                            <div v-if="b.delivery_log" class="mt-2 flex items-center gap-3 text-xs">
                                <span class="text-[#91918c]">Push:</span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 font-medium text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                                    {{ b.delivery_log.ntfy_success }} berhasil
                                </span>
                                <span v-if="b.delivery_log.ntfy_failed > 0" class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 font-medium text-red-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500" />
                                    {{ b.delivery_log.ntfy_failed }} gagal
                                </span>
                            </div>
                        </div>
                        <button
                            v-if="b.type === 'notification'"
                            @click="resend(b.id)"
                            class="shrink-0 rounded-full border border-[#dadad3] bg-white px-3 py-1.5 text-xs font-bold text-[#62625b] hover:bg-[#f6f6f3] transition-colors"
                        >
                            Kirim Ulang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
