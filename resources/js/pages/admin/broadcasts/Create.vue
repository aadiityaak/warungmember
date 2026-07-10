<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes/admin';
import { index as broadcastsIndex } from '@/routes/admin/broadcasts';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Broadcast', href: broadcastsIndex() },
            { title: 'Buat Broadcast' },
        ] as BreadcrumbItem[],
    },
});

const { memberCount, segmentCounts, iconOptions } = defineProps<{
    memberCount: number;
    segmentCounts: Record<string, number>;
    iconOptions: { value: string; label: string; d: string }[];
}>();

const form = useForm({
    type: 'notification',
    title: '',
    body: '',
    segment: 'all',
    segment_value: undefined as number | undefined,
    icon: 'umum',
});

const segments = computed(() => [
    { value: 'all', label: 'Semua Member', count: segmentCounts.all },
    { value: 'points_above', label: 'Poin diatas', count: segmentCounts.points_above },
    { value: 'new_member', label: 'Member Baru', count: segmentCounts.new_member },
    { value: 'deposit_above', label: 'Deposit diatas', count: segmentCounts.deposit_above },
]);

const estimatedCount = ref(memberCount);
let estimateTimer: ReturnType<typeof setTimeout> | null = null;

watch([() => form.segment, () => form.segment_value], () => {
    if (form.segment === 'all') {
        estimatedCount.value = memberCount;
        return;
    }
    if (estimateTimer) clearTimeout(estimateTimer);
    estimateTimer = setTimeout(async () => {
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(route('admin.broadcasts.estimate'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token ?? '' },
                body: JSON.stringify({ segment: form.segment, segment_value: form.segment_value }),
            });
            const data = await res.json();
            estimatedCount.value = data.count ?? 0;
        } catch { /* ignore */ }
    }, 400);
});

function submit() {
    form.post(route('admin.broadcasts.store'), {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Buat Broadcast" />
    <div class="flex flex-1 flex-col gap-6 p-6">
        <div class="mb-6 rounded-2xl border border-[#dadad3] bg-white">
            <div class="overflow-hidden rounded-t-2xl border-b border-[#dadad3] px-5 py-4">
                <h3 class="text-base font-bold leading-[1.2] text-[#000000]">Buat Broadcast</h3>
            </div>
            <form @submit.prevent="submit" class="px-5 py-4 space-y-5">
                <div>
                    <label class="mb-1 block text-sm font-bold leading-[1.4] text-[#000000]">Tipe</label>
                    <div class="flex gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" v-model="form.type" value="notification" class="accent-[#E22625]" />
                            <span class="text-sm text-[#000000]">Notifikasi</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" v-model="form.type" value="email" class="accent-[#E22625]" />
                            <span class="text-sm text-[#000000]">Email</span>
                        </label>
                    </div>
                    <p v-if="form.errors.type" class="mt-1 text-xs text-[#E22625]">{{ form.errors.type }}</p>
                </div>
                <div v-if="form.type === 'notification'">
                    <label class="mb-1 block text-sm font-bold leading-[1.4] text-[#000000]">Ikon Notifikasi</label>
                    <div class="grid grid-cols-5 gap-2">
                        <label v-for="opt in iconOptions" :key="opt.value" class="flex cursor-pointer flex-col items-center gap-1.5 rounded-xl border border-[#dadad3] px-2 py-3 transition-colors hover:bg-[#f6f6f3]" :class="{ 'border-[#E22625] bg-[#E22625]/5': form.icon === opt.value }">
                            <input type="radio" v-model="form.icon" :value="opt.value" class="sr-only" />
                            <svg class="h-6 w-6" :class="form.icon === opt.value ? 'text-[#E22625]' : 'text-[#62625b]'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="opt.d" />
                            </svg>
                            <span class="text-xs font-semibold" :class="form.icon === opt.value ? 'text-[#E22625]' : 'text-[#62625b]'">{{ opt.label }}</span>
                        </label>
                    </div>
                    <p v-if="form.errors.icon" class="mt-1 text-xs text-[#E22625]">{{ form.errors.icon }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-bold leading-[1.4] text-[#000000]">Target Member</label>
                    <div class="space-y-2">
                        <label v-for="s in segments" :key="s.value" class="flex items-center gap-3 rounded-xl border border-[#dadad3] px-4 py-3 cursor-pointer transition-colors hover:bg-[#f6f6f3]" :class="{ 'border-[#E22625] bg-[#E22625]/5': form.segment === s.value }">
                            <input type="radio" v-model="form.segment" :value="s.value" class="accent-[#E22625] shrink-0" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-[#000000]">{{ s.label }}</p>
                                <p v-if="form.segment === s.value" class="text-xs text-[#91918c] mt-0.5">Estimasi: {{ estimatedCount }} member</p>
                            </div>
                        </label>
                    </div>
                    <div v-if="form.segment === 'points_above'" class="mt-2">
                        <label class="text-xs font-semibold text-[#62625b]">Minimal Poin</label>
                        <input v-model="form.segment_value" type="number" min="0" step="100" class="mt-1 w-full rounded-full border-0 bg-[#f6f6f3] px-4 py-2 text-sm leading-[1.4] text-[#000000] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c8c8c1]" placeholder="Contoh: 5000" />
                    </div>
                    <div v-else-if="form.segment === 'new_member'" class="mt-2">
                        <label class="text-xs font-semibold text-[#62625b]">Hari Terakhir</label>
                        <input v-model="form.segment_value" type="number" min="1" class="mt-1 w-full rounded-full border-0 bg-[#f6f6f3] px-4 py-2 text-sm leading-[1.4] text-[#000000] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c8c8c1]" placeholder="Contoh: 30" />
                    </div>
                    <div v-else-if="form.segment === 'deposit_above'" class="mt-2">
                        <label class="text-xs font-semibold text-[#62625b]">Minimal Deposit</label>
                        <input v-model="form.segment_value" type="number" min="0" class="mt-1 w-full rounded-full border-0 bg-[#f6f6f3] px-4 py-2 text-sm leading-[1.4] text-[#000000] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c8c8c1]" placeholder="Contoh: 10000" />
                    </div>
                    <p v-if="form.errors.segment" class="mt-1 text-xs text-[#E22625]">{{ form.errors.segment }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-bold leading-[1.4] text-[#000000]">Judul</label>
                    <input v-model="form.title" class="w-full rounded-full border-0 bg-[#f6f6f3] px-4 py-2.5 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c8c8c1]" placeholder="Judul broadcast" />
                    <p v-if="form.errors.title" class="mt-1 text-xs text-[#E22625]">{{ form.errors.title }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-bold leading-[1.4] text-[#000000]">Pesan</label>
                    <textarea v-model="form.body" rows="5" class="w-full rounded-xl border-0 bg-[#f6f6f3] px-4 py-2.5 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c8c8c1] resize-none" placeholder="Isi pesan broadcast" />
                    <p v-if="form.errors.body" class="mt-1 text-xs text-[#E22625]">{{ form.errors.body }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <Button type="submit" :disabled="form.processing" class="bg-[#E22625] text-white hover:opacity-90">{{ form.processing ? 'Mengirim...' : 'Kirim Broadcast' }}</Button>
                    <Link :href="broadcastsIndex()" class="text-sm font-semibold text-[#91918c] hover:text-[#000000] transition-colors">Batal</Link>
                </div>
            </form>
        </div>
    </div>
</template>
