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
            { title: 'Kasir' },
        ] as BreadcrumbItem[],
    },
});

const { kasirs } = defineProps<{
    kasirs: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            created_at: string;
            outlet: { name: string } | null;
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
    if (confirm('Hapus kasir ini?')) {
        deleteForm.delete(route('admin.kasir.destroy', id));
    }
}

const paginationPages = computed(() => {
    const current = kasirs.current_page;
    const last = kasirs.last_page;
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
    <Head title="Kelola Kasir" />

    <div class="mx-6 pt-6">
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Kelola Kasir
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Daftar akun kasir Warung Mas Mbull
            </p>
        </header>

        <div class="mb-6 flex gap-2">
            <Button v-if="isAdmin" as="child">
                <Link :href="route('admin.kasir.create')">+ Tambah Kasir</Link>
            </Button>
            <Button as="child" variant="outline">
                <Link :href="route('admin.outlets.index')">Outlet</Link>
            </Button>
        </div>

        <div v-if="kasirs.data.length === 0" class="rounded-2xl bg-[#f6f6f3] py-16 text-center">
            <p class="text-sm leading-[1.4] text-[#62625b]">Belum ada kasir ditambahkan.</p>
        </div>

        <div v-else class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#dadad3]">
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Nama</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Outlet</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Email</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden sm:table-cell">Dibuat</th>
                        <th v-if="isAdmin" class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="kasir in kasirs.data"
                        :key="kasir.id"
                        class="border-b border-[#e5e5e0] last:border-0 transition-colors hover:bg-[#fbfbf9]"
                    >
                        <td class="px-4 py-3">
                            <p class="text-sm leading-[1.4] font-semibold text-[#000000]">{{ kasir.name }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm leading-[1.4] text-[#62625b]">{{ kasir.outlet?.name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm leading-[1.4] text-[#62625b]">{{ kasir.email }}</span>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="text-sm leading-[1.4] text-[#91918c]">{{ new Date(kasir.created_at).toLocaleDateString('id-ID') }}</span>
                        </td>
                        <td v-if="isAdmin" class="px-2 py-3">
                            <div class="flex gap-1">
                                <Link
                                    :href="route('admin.kasir.edit', kasir.id)"
                                    class="inline-flex h-8 items-center rounded-full bg-[#f6f6f3] px-3 text-xs font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#e5e5e0]"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="destroy(kasir.id)"
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
            <div v-if="kasirs.last_page > 1" class="flex items-center justify-between border-t border-[#dadad3] px-5 py-3">
                <span class="text-sm leading-[1.4] text-[#62625b]">
                    {{ kasirs.from }}-{{ kasirs.to }} dari {{ kasirs.total }}
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
                            :href="route('admin.kasir.index', { page: p })"
                            :class="[
                                'inline-flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold leading-[1] transition-colors',
                                p === kasirs.current_page
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
