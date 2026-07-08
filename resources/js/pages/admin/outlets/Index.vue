<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Outlet' },
        ] as BreadcrumbItem[],
    },
});

const { outlets } = defineProps<{
    outlets: {
        data: Array<{
            id: number;
            name: string;
            address: string | null;
            phone: string | null;
            is_active: boolean;
            kasir: { name: string } | null;
        }>;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
    };
}>();

const deleteForm = useForm({});

const page = usePage();
const isAdmin = (page.props.auth?.user as Record<string, unknown>)?.role === 'admin';

function destroy(id: number) {
    if (confirm('Hapus outlet ini?')) {
        deleteForm.delete(route('admin.outlets.destroy', id));
    }
}

const paginationPages = computed(() => {
    const current = outlets.current_page;
    const last = outlets.last_page;
    const pages: (number | '...')[] = [];

    if (last <= 9) {
        for (let i = 1; i <= last; i++) pages.push(i);
        return pages;
    }

    for (let i = 1; i <= 3; i++) pages.push(i);

    if (current > 4) pages.push('...');

    const start = Math.max(4, current - 1);
    const end = Math.min(last - 3, current + 1);

    for (let i = start; i <= end; i++) pages.push(i);

    if (current < last - 3) pages.push('...');

    for (let i = last - 2; i <= last; i++) pages.push(i);

    return pages;
});
</script>

<template>
    <Head title="Kelola Outlet" />

    <div class="mx-6 pt-6">
        <!-- Header -->
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Kelola Outlet
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Daftar outlet / cabang Warung Mas Mbull
            </p>
        </header>

        <!-- Toolbar -->
        <div class="mb-6 flex gap-2">
            <Button as="child">
                <Link :href="route('admin.outlets.create')">+ Tambah Outlet</Link>
            </Button>
            <Button as="child" v-if="isAdmin" variant="outline">
                <Link :href="route('admin.kasir.index')">Kelola Kasir</Link>
            </Button>
        </div>

        <!-- Empty -->
        <div v-if="outlets.data.length === 0" class="rounded-2xl bg-[#f6f6f3] py-16 text-center">
            <p class="text-sm leading-[1.4] text-[#62625b]">Belum ada outlet ditambahkan.</p>
        </div>

        <!-- Table -->
        <div v-else class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#dadad3]">
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Nama</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Kasir</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden sm:table-cell">Alamat</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden md:table-cell">Telepon</th>
                        <th class="px-4 py-3 text-center text-sm font-bold leading-[1.4] text-[#000000] w-20">Status</th>
                        <th v-if="isAdmin" class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="outlet in outlets.data"
                        :key="outlet.id"
                        class="border-b border-[#e5e5e0] last:border-0 transition-colors hover:bg-[#fbfbf9]"
                    >
                        <!-- Nama -->
                        <td class="px-4 py-3">
                            <p class="text-sm leading-[1.4] font-semibold text-[#000000]">{{ outlet.name }}</p>
                        </td>
                        <!-- Kasir -->
                        <td class="px-4 py-3">
                            <span class="text-sm leading-[1.4] text-[#62625b]">{{ outlet.kasir?.name ?? '-' }}</span>
                        </td>
                        <!-- Alamat -->
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="text-sm leading-[1.4] text-[#62625b] line-clamp-2">{{ outlet.address ?? '-' }}</span>
                        </td>
                        <!-- Telepon -->
                        <td class="px-4 py-3 hidden md:table-cell">
                            <span class="text-sm leading-[1.4] text-[#62625b]">{{ outlet.phone ?? '-' }}</span>
                        </td>
                        <!-- Status -->
                        <td class="px-4 py-3 text-center">
                            <span
                                :class="[
                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold leading-[1.4]',
                                    outlet.is_active
                                        ? 'bg-[#E22625]/10 text-[#E22625]'
                                        : 'bg-[#e5e5e0] text-[#91918c]',
                                ]"
                            >
                                {{ outlet.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <!-- Aksi -->
                        <td v-if="isAdmin" class="px-2 py-3">
                            <div class="flex gap-1">
                                <Link
                                    :href="route('admin.outlets.edit', outlet.id)"
                                    class="inline-flex h-8 items-center rounded-full bg-[#f6f6f3] px-3 text-xs font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#e5e5e0]"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="destroy(outlet.id)"
                                    :disabled="deleteForm.processing"
                                    class="inline-flex h-8 items-center rounded-full bg-[#f6f6f3] px-3 text-xs font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#E22625] hover:text-white"
                                >
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="outlets.last_page > 1" class="flex items-center justify-between border-t border-[#dadad3] px-5 py-3">
                <span class="text-sm leading-[1.4] text-[#62625b]">
                    {{ outlets.from }}-{{ outlets.to }} dari {{ outlets.total }}
                </span>
                <div class="flex gap-1">
                    <template v-for="(p, idx) in paginationPages" :key="idx">
                        <span
                            v-if="p === '...'"
                            class="inline-flex h-9 w-9 items-center justify-center text-sm font-bold leading-[1] text-[#62625b]"
                        >
                            ...
                        </span>
                        <Link
                            v-else
                            :href="route('admin.outlets.index', { page: p })"
                            :class="[
                                'inline-flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold leading-[1] transition-colors',
                                p === outlets.current_page
                                    ? 'bg-[#000000] text-white'
                                    : 'text-[#000000] hover:bg-[#f6f6f3]',
                            ]"
                        >
                            {{ p }}
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
