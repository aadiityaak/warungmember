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
            { title: 'Voucher' },
        ] as BreadcrumbItem[],
    },
});

const { vouchers } = defineProps<{
    vouchers: {
        data: Array<{
            id: number;
            code: string;
            type: string;
            discount_type: string;
            discount_value: number;
            max_discount: number | null;
            is_active: boolean;
        }>;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
    };
}>();

const form = useForm({});
const page = usePage();
const isAdmin = (page.props.auth?.user as Record<string, unknown>)?.role === 'admin';

const typeLabels: Record<string, string> = {
    birthday: 'Ulang Tahun',
    golden_hour: 'Golden Hour',
    manual: 'Manual',
};

function destroy(id: number) {
    if (confirm('Hapus voucher ini?')) {
        form.delete(route('admin.vouchers.destroy', id));
    }
}

const paginationPages = computed(() => {
    const current = vouchers.current_page;
    const last = vouchers.last_page;
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
    <Head title="Kelola Voucher" />

    <div class="mx-6 pt-6">
        <!-- Header -->
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Kelola Voucher
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Daftar voucher promo dan diskon
            </p>
        </header>

        <!-- Toolbar -->
        <div class="mb-6">
            <Button v-if="isAdmin" as="child">
                <Link :href="route('admin.vouchers.create')">+ Tambah Voucher</Link>
            </Button>
        </div>

        <!-- Empty -->
        <div v-if="vouchers.data.length === 0" class="rounded-2xl bg-[#f6f6f3] py-16 text-center">
            <p class="text-sm leading-[1.4] text-[#62625b]">Belum ada voucher ditambahkan.</p>
        </div>

        <!-- Table -->
        <div v-else class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#dadad3]">
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Kode</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Tipe</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Diskon</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden sm:table-cell">Maks. Diskon</th>
                        <th class="px-4 py-3 text-center text-sm font-bold leading-[1.4] text-[#000000] w-20">Status</th>
                        <th v-if="isAdmin" class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] w-16">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="v in vouchers.data"
                        :key="v.id"
                        class="border-b border-[#e5e5e0] last:border-0 transition-colors hover:bg-[#fbfbf9]"
                    >
                        <td class="px-4 py-3">
                            <span class="text-sm leading-[1.4] font-mono font-semibold text-[#000000]">{{ v.code }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm leading-[1.4] text-[#62625b]">{{ typeLabels[v.type] ?? v.type }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm font-semibold leading-[1.4] text-[#E22625]">
                                {{ v.discount_type === 'percent' ? `${v.discount_value}%` : `Rp ${v.discount_value.toLocaleString('id-ID')}` }}
                            </span>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="text-sm leading-[1.4] text-[#62625b]">
                                {{ v.max_discount ? `Rp ${v.max_discount.toLocaleString('id-ID')}` : '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span
                                :class="[
                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold leading-[1.4]',
                                    v.is_active
                                        ? 'bg-[#E22625]/10 text-[#E22625]'
                                        : 'bg-[#e5e5e0] text-[#91918c]',
                                ]"
                            >
                                {{ v.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td v-if="isAdmin" class="px-2 py-3">
                            <button
                                @click="destroy(v.id)"
                                :disabled="form.processing"
                                class="inline-flex h-8 items-center rounded-full bg-[#f6f6f3] px-3 text-xs font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#E22625] hover:text-white"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="vouchers.last_page > 1" class="flex items-center justify-between border-t border-[#dadad3] px-5 py-3">
                <span class="text-sm leading-[1.4] text-[#62625b]">
                    {{ vouchers.from }}-{{ vouchers.to }} dari {{ vouchers.total }}
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
                            :href="route('admin.vouchers.index', { page: p })"
                            :class="[
                                'inline-flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold leading-[1] transition-colors',
                                p === vouchers.current_page
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
