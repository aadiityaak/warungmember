<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Reward' },
        ] as BreadcrumbItem[],
    },
});

const { rewards } = defineProps<{
    rewards: Array<{
        id: number;
        name: string;
        description: string | null;
        image: string | null;
        points_required: number;
        stock: number | null;
        is_active: boolean;
    }>;
}>();

const form = useForm({});
const page = usePage();
const isAdmin = (page.props.auth?.user as Record<string, unknown>)?.role === 'admin';

function destroy(id: number) {
    if (confirm('Hapus reward ini?')) {
        form.delete(route('admin.rewards.destroy', id));
    }
}
</script>

<template>
    <Head title="Kelola Reward" />

    <div class="mx-6 pt-6">
        <!-- Header -->
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Kelola Reward
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Daftar reward yang bisa ditukar member
            </p>
        </header>

        <!-- Toolbar -->
        <div class="mb-6">
            <Button v-if="isAdmin" as="child">
                <Link :href="route('admin.rewards.create')">+ Tambah Reward</Link>
            </Button>
        </div>

        <!-- Empty -->
        <div v-if="rewards.length === 0" class="rounded-2xl bg-[#f6f6f3] py-16 text-center">
            <p class="text-sm leading-[1.4] text-[#62625b]">Belum ada reward ditambahkan.</p>
        </div>

        <!-- Table -->
        <div v-else class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#dadad3]">
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] w-16">Gambar</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Nama</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden md:table-cell">Deskripsi</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Poin</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden sm:table-cell">Stok</th>
                        <th class="px-4 py-3 text-center text-sm font-bold leading-[1.4] text-[#000000] w-20">Status</th>
                        <th v-if="isAdmin" class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="reward in rewards"
                        :key="reward.id"
                        class="border-b border-[#e5e5e0] last:border-0 transition-colors hover:bg-[#fbfbf9]"
                    >
                        <!-- Thumbnail -->
                        <td class="px-4 py-3">
                            <img
                                v-if="reward.image"
                                :src="reward.image"
                                :alt="reward.name"
                                class="h-10 w-10 rounded-lg object-cover border border-[#dadad3]"
                            />
                            <div
                                v-else
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#f6f6f3]"
                            >
                                <svg class="h-4 w-4 text-[#91918c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            </div>
                        </td>
                        <!-- Nama -->
                        <td class="px-4 py-3">
                            <p class="text-sm leading-[1.4] font-semibold text-[#000000]">{{ reward.name }}</p>
                        </td>
                        <!-- Deskripsi -->
                        <td class="px-4 py-3 hidden md:table-cell">
                            <span class="text-sm leading-[1.4] text-[#62625b] line-clamp-2">{{ reward.description ?? '-' }}</span>
                        </td>
                        <!-- Poin -->
                        <td class="px-4 py-3">
                            <span class="text-sm font-semibold leading-[1.4] text-[#E22625]">{{ reward.points_required.toLocaleString('id-ID') }}</span>
                        </td>
                        <!-- Stok -->
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="text-sm leading-[1.4] text-[#62625b]">{{ reward.stock !== null ? reward.stock : '∞' }}</span>
                        </td>
                        <!-- Status -->
                        <td class="px-4 py-3 text-center">
                            <span
                                :class="[
                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold leading-[1.4]',
                                    reward.is_active
                                        ? 'bg-[#E22625]/10 text-[#E22625]'
                                        : 'bg-[#e5e5e0] text-[#91918c]',
                                ]"
                            >
                                {{ reward.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <!-- Aksi -->
                        <td v-if="isAdmin" class="px-2 py-3">
                            <div class="flex gap-1">
                                <Link
                                    :href="route('admin.rewards.edit', reward.id)"
                                    class="inline-flex h-8 items-center rounded-full bg-[#f6f6f3] px-3 text-xs font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#e5e5e0]"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="destroy(reward.id)"
                                    :disabled="form.processing"
                                    class="inline-flex h-8 items-center rounded-full bg-[#f6f6f3] px-3 text-xs font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#E22625] hover:text-white"
                                >
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
