<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { useNtfy } from '@/composables/useNtfy';

defineOptions({ layout: MemberLayout });

const ntfy = useNtfy();

onMounted(async () => {
    await ntfy.init();
});

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
    promo: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
    voucher: 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z',
    poin: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    deposit: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
    order: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
    order_status: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
    umum: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
};

const typeLabels: Record<string, string> = {
    promo: 'Promo',
    voucher: 'Voucher',
    poin: 'Poin',
    deposit: 'Deposit',
    order: 'Pesanan',
    order_status: 'Status',
    umum: 'Umum',
};

function markAsRead(notificationId: number) {
    router.post(route('member.notifications.read', notificationId));
}

function markAllRead() {
    router.post(route('member.notifications.readAll'));
}

function formatTime(dateStr: string): string {
    const d = new Date(dateStr);
    const now = new Date();
    const diffMs = now.getTime() - d.getTime();
    const diffMin = Math.floor(diffMs / 60000);
    const diffHr = Math.floor(diffMs / 3600000);
    const diffDay = Math.floor(diffMs / 86400000);

    if (diffMin < 1) return 'Baru saja';
    if (diffMin < 60) return `${diffMin} menit lalu`;
    if (diffHr < 24) return `${diffHr} jam lalu`;
    if (diffDay < 7) return `${diffDay} hari lalu`;
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function parseBody(body: string): Array<{ text: string; highlight: boolean }> {
    const parts: Array<{ text: string; highlight: boolean }> = [];
    const regex = /\+\d+\s*poin!?/gi;
    let lastIdx = 0;
    let match: RegExpExecArray | null;

    while ((match = regex.exec(body)) !== null) {
        if (match.index > lastIdx) {
            parts.push({ text: body.slice(lastIdx, match.index), highlight: false });
        }
        parts.push({ text: match[0], highlight: true });
        lastIdx = regex.lastIndex;
    }

    if (lastIdx < body.length) {
        parts.push({ text: body.slice(lastIdx), highlight: false });
    }

    return parts.length ? parts : [{ text: body, highlight: false }];
}
</script>

<template>
    <Head title="Notifikasi" />

    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div>
            <h1 class="text-[22px] font-semibold leading-[1.25] text-[#000000]">Notifikasi</h1>
            <p class="mt-1 text-sm leading-[1.4] text-[#62625b]">Pusat pemberitahuan & info terbaru</p>
        </div>

        <!-- Push Notification Status -->
        <div
            v-if="ntfy.supported"
            class="rounded-2xl border px-4 py-3.5"
            :class="ntfy.subscribed ? 'border-[#22c55e] bg-[#f0fdf4]' : 'border-[#dadad3] bg-white'"
        >
            <div v-if="ntfy.loading" class="flex items-center gap-3 animate-pulse">
                <div class="h-10 w-10 shrink-0 rounded-full bg-[#dadad3]" />
                <div class="flex-1 space-y-2">
                    <div class="h-4 w-32 rounded bg-[#dadad3]" />
                    <div class="h-3 w-56 rounded bg-[#dadad3]" />
                </div>
            </div>
            <template v-else>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                            :class="ntfy.subscribed ? 'bg-[#22c55e]/10' : 'bg-[#f6f6f3]'"
                        >
                            <svg
                                v-if="ntfy.subscribed"
                                class="h-5 w-5 text-[#22c55e]"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <svg
                                v-else
                                class="h-5 w-5 text-[#91918c]"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold leading-[1.4] text-[#000000]">
                                {{ ntfy.subscribed ? 'Notifikasi Aktif' : 'Notifikasi Belum Aktif' }}
                            </p>
                            <p v-if="ntfy.subscribed" class="text-xs leading-[1.4] text-[#62625b] mt-0.5">
                                Kamu akan menerima notifikasi langsung di perangkat ini
                            </p>
                            <p v-else-if="ntfy.error" class="text-xs leading-[1.4] text-[#62625b] mt-0.5">
                                {{ ntfy.error }}
                            </p>
                            <p v-else class="text-xs leading-[1.4] text-[#62625b] mt-0.5">
                                Aktifkan notifikasi untuk mendapat info promo & pesanan real-time
                            </p>
                        </div>
                    </div>
                    <button
                        v-if="!ntfy.subscribed"
                        @click="ntfy.subscribe()"
                        class="shrink-0 rounded-full bg-[#E22625] px-4 py-1.5 text-xs font-bold leading-[1] text-white transition-colors hover:opacity-90"
                    >
                        Aktifkan
                    </button>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div
            v-if="!notifications || notifications.data.length === 0"
            class="flex flex-col items-center py-20"
        >
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[#f6f6f3]">
                <svg class="h-7 w-7 text-[#91918c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <p class="mt-4 text-sm font-semibold leading-[1.4] text-[#000000]">Belum ada notifikasi</p>
            <p class="mt-1 text-sm leading-[1.4] text-[#62625b]">Kamu akan menerima info promo & pesanan di sini</p>
        </div>

        <!-- Notification List -->
        <div v-else class="flex flex-col gap-3">
            <!-- Mark All Read -->
            <div v-if="notifications.data.some((n: any) => !n.read_at)" class="flex justify-end">
                <button
                    @click="markAllRead"
                    class="inline-flex items-center gap-1.5 rounded-full bg-[#f6f6f3] px-3 py-1.5 text-xs font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#000000] hover:text-white"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Tandai Semua Dibaca
                </button>
            </div>
            <button
                v-for="n in notifications.data"
                :key="n.id"
                @click="!n.read_at ? markAsRead(n.id) : undefined"
                class="flex w-full gap-3 rounded-2xl px-4 py-3.5 text-left transition-colors"
                :class="n.read_at
                    ? 'bg-white border border-[#dadad3]'
                    : 'bg-[#fbfbf9] border border-[#dadad3]'
                "
            >
                <!-- Icon -->
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                    :class="n.read_at ? 'bg-[#f6f6f3]' : 'bg-[#000000]'"
                >
                    <svg
                        class="h-4 w-4"
                        :class="n.read_at ? 'text-[#62625b]' : 'text-white'"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            :d="typeIcons[n.type] ?? typeIcons.umum"
                        />
                    </svg>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-semibold leading-[1.4] text-[#91918c]">
                            {{ typeLabels[n.type] ?? n.type }}
                        </span>
                        <span
                            v-if="!n.read_at"
                            class="inline-block h-1.5 w-1.5 rounded-full bg-[#e60023]"
                        />
                    </div>
                    <p
                        class="mt-0.5 text-sm leading-[1.4] text-[#000000]"
                        :class="{ 'font-semibold': !n.read_at }"
                    >
                        {{ n.title }}
                    </p>
                    <p class="mt-0.5 text-sm leading-[1.4] text-[#62625b] line-clamp-2">
                        <template v-for="(part, i) in parseBody(n.body)" :key="i">
                            <span v-if="part.highlight" class="font-bold text-[#d4a017]">{{ part.text }}</span>
                            <span v-else>{{ part.text }}</span>
                        </template>
                    </p>
                    <p class="mt-1 text-xs leading-[1.4] text-[#91918c]">
                        {{ formatTime(n.created_at) }}
                    </p>
                </div>
            </button>
        </div>
    </div>
</template>
