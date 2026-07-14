<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import type { User } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Member', href: route('admin.members.index') },
        ] as BreadcrumbItem[],
    },
});

const { members, filters, outlets } = defineProps<{
    members: { data: User[]; current_page: number; last_page: number; from: number; to: number; total: number };
    filters: { search?: string; sort?: string; direction?: string; outlet_id?: string; date_from?: string; date_to?: string; status?: string };
    outlets: { id: number; name: string }[];
}>();

const form = useForm({
    search: filters.search ?? '',
    outlet_id: filters.outlet_id ?? '',
    date_from: filters.date_from ?? '',
    date_to: filters.date_to ?? '',
    status: filters.status ?? '',
});

const deleteForm = useForm({});

const page = usePage();
const isAdmin = (page.props.auth?.user as Record<string, unknown>)?.role === 'admin';

const currentSort = filters.sort ?? 'created_at';
const currentDirection = filters.direction ?? 'desc';

function sortUrl(column: string): string {
    const params: Record<string, string | number> = {};
    if (form.search) params.search = form.search;
    if (form.outlet_id) params.outlet_id = form.outlet_id;
    if (form.date_from) params.date_from = form.date_from;
    if (form.date_to) params.date_to = form.date_to;
    if (form.status) params.status = form.status;
    params.sort = column;
    params.direction = currentSort === column && currentDirection === 'asc' ? 'desc' : 'asc';
    return route('admin.members.index', params);
}

function sortIndicator(column: string): string {
    if (currentSort !== column) return '';
    return currentDirection === 'asc' ? ' \u2191' : ' \u2193';
}

function submit() {
    form.get(route('admin.members.index'), { preserveState: true, replace: true });
}

function destroy(id: number) {
    if (confirm('Nonaktifkan member ini? Member tidak akan bisa login.')) {
        deleteForm.delete(route('admin.members.destroy', id));
    }
}

const paginationPages = computed(() => {
    const current = members.current_page;
    const last = members.last_page;
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
    <Head title="Manajemen Member" />

    <div class="mx-6 pt-6">
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Manajemen Member
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Kelola data member WarungMember
            </p>
        </header>

        <div class="mb-6 flex flex-wrap items-center gap-3">
            <form @submit.prevent="submit" class="min-w-[200px] flex-1">
                <input
                    v-model="form.search"
                    placeholder="Cari nama atau email..."
                    class="w-full rounded-full border-0 bg-[#f6f6f3] px-4 py-3 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c8c8c1]"
                />
            </form>
            <select
                v-model="form.outlet_id"
                @change="submit"
                class="rounded-full border-0 bg-[#f6f6f3] px-4 py-3 text-sm leading-[1.4] text-[#000000] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c8c8c1]"
            >
                <option value="">Semua Outlet</option>
                <option v-for="outlet in outlets" :key="outlet.id" :value="outlet.id">{{ outlet.name }}</option>
            </select>
            <select
                v-model="form.status"
                @change="submit"
                class="rounded-full border-0 bg-[#f6f6f3] px-4 py-3 text-sm leading-[1.4] text-[#000000] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c8c8c1]"
            >
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
            </select>
            <input
                v-model="form.date_from"
                type="date"
                @change="submit"
                class="rounded-full border-0 bg-[#f6f6f3] px-4 py-3 text-sm leading-[1.4] text-[#000000] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c8c8c1]"
            />
            <input
                v-model="form.date_to"
                type="date"
                @change="submit"
                class="rounded-full border-0 bg-[#f6f6f3] px-4 py-3 text-sm leading-[1.4] text-[#000000] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c8c8c1]"
            />
            <Button as="child">
                <Link :href="route('admin.members.create')">+ Tambah</Link>
            </Button>
        </div>

        <div v-if="members.data.length === 0" class="rounded-2xl bg-[#f6f6f3] py-16 text-center">
            <p class="text-sm leading-[1.4] text-[#62625b]">Belum ada member terdaftar.</p>
        </div>

        <div v-else class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#dadad3]">
                        <th class="px-5 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">
                            <Link :href="sortUrl('name')" class="hover:text-[#E22625] transition-colors">Nama{{ sortIndicator('name') }}</Link>
                        </th>
                        <th class="px-5 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">
                            <Link :href="sortUrl('email')" class="hover:text-[#E22625] transition-colors">Email{{ sortIndicator('email') }}</Link>
                        </th>
                        <th class="px-5 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">ID Member</th>
                        <th class="px-5 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">
                            <Link :href="sortUrl('total_points')" class="hover:text-[#E22625] transition-colors">Poin{{ sortIndicator('total_points') }}</Link>
                        </th>
                        <th class="px-5 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">
                            <Link :href="sortUrl('created_at')" class="hover:text-[#E22625] transition-colors">Tanggal Daftar{{ sortIndicator('created_at') }}</Link>
                        </th>
                        <th class="px-5 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="member in members.data" :key="member.id" class="border-b border-[#e5e5e0] last:border-0 hover:bg-[#fbfbf9] transition-colors">
                        <td class="px-5 py-3 text-sm leading-[1.4] font-semibold text-[#000000]">{{ member.name }}</td>
                        <td class="px-5 py-3 text-sm leading-[1.4] text-[#62625b]">{{ member.email }}</td>
                        <td class="px-5 py-3 text-sm leading-[1.4] font-mono font-semibold text-[#000000]">{{ member.member?.member_code ?? '-' }}</td>
                        <td class="px-5 py-3 text-sm leading-[1.4] font-semibold text-[#E22625]">{{ member.member?.total_points?.toLocaleString('id-ID') ?? '0' }}</td>
                        <td class="px-5 py-3 text-sm leading-[1.4] text-[#62625b]">{{ new Date(member.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}</td>
                        <td class="px-5 py-3">
                            <div class="flex gap-1.5">
                                <Link
                                    :href="route('admin.members.show', member.id)"
                                    class="inline-flex h-9 items-center rounded-full bg-[#f6f6f3] px-4 text-sm font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#e5e5e0]"
                                >
                                    Detail
                                </Link>
                                <Link
                                    :href="route('admin.members.edit', member.id)"
                                    class="inline-flex h-9 items-center rounded-full bg-[#f6f6f3] px-4 text-sm font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#e5e5e0]"
                                >
                                    Edit
                                </Link>
                                <button
                                    v-if="isAdmin"
                                    @click="destroy(member.id)"
                                    :disabled="deleteForm.processing"
                                    class="inline-flex h-9 items-center rounded-full bg-[#f6f6f3] px-4 text-sm font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#e60023] hover:text-white"
                                >
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="members.last_page > 1" class="flex items-center justify-between border-t border-[#dadad3] px-5 py-3">
                <span class="text-sm leading-[1.4] text-[#62625b]">
                    {{ members.from }}-{{ members.to }} dari {{ members.total }}
                </span>
                <div class="flex gap-1">
                    <template v-for="(page, idx) in paginationPages" :key="idx">
                        <span
                            v-if="page === '...'"
                            class="inline-flex h-9 w-9 items-center justify-center text-sm font-bold leading-[1] text-[#62625b]"
                        >
                            ...
                        </span>
                        <Link
                            v-else
                            :href="route('admin.members.index', { page, search: filters.search, sort: currentSort, direction: currentDirection, outlet_id: filters.outlet_id, date_from: filters.date_from, date_to: filters.date_to, status: filters.status })"
                            :class="[
                                'inline-flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold leading-[1] transition-colors',
                                page === members.current_page
                                    ? 'bg-[#000000] text-white'
                                    : 'text-[#000000] hover:bg-[#f6f6f3]',
                            ]"
                        >
                            {{ page }}
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
