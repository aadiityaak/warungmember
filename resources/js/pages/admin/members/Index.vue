<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
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

const { members, filters } = defineProps<{
    members: { data: User[]; current_page: number; last_page: number; from: number; to: number; total: number };
    filters: { search?: string; sort?: string; direction?: string };
}>();

const form = useForm({
    search: filters.search ?? '',
});

const deleteForm = useForm({});

const currentSort = filters.sort ?? 'created_at';
const currentDirection = filters.direction ?? 'desc';

function sortUrl(column: string): string {
    const params: Record<string, string | number> = {};
    if (form.search) params.search = form.search;
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
</script>

<template>
    <Head title="Manajemen Member" />

    <div class="mx-6 pt-6">
        <!-- Header -->
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Manajemen Member
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Kelola data member WarungMember
            </p>
        </header>

        <!-- Toolbar -->
        <div class="mb-6 flex items-center gap-3">
            <form @submit.prevent="submit" class="flex-1">
                <input
                    v-model="form.search"
                    placeholder="Cari nama atau email..."
                    class="w-full rounded-full border-0 bg-[#f6f6f3] px-4 py-3 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c8c8c1]"
                />
            </form>
            <Button as="child">
                <Link :href="route('admin.members.create')">+ Tambah</Link>
            </Button>
        </div>

        <!-- Member List -->
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

            <!-- Pagination -->
            <div v-if="members.last_page > 1" class="flex items-center justify-between border-t border-[#dadad3] px-5 py-3">
                <span class="text-sm leading-[1.4] text-[#62625b]">
                    {{ members.from }}-{{ members.to }} dari {{ members.total }}
                </span>
                <div class="flex gap-1">
                    <Link
                        v-for="page in members.last_page"
                        :key="page"
                        :href="route('admin.members.index', { page, search: filters.search, sort: currentSort, direction: currentDirection })"
                        :class="[
                            'inline-flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold leading-[1] transition-colors',
                            page === members.current_page
                                ? 'bg-[#000000] text-white'
                                : 'text-[#000000] hover:bg-[#f6f6f3]',
                        ]"
                    >
                        {{ page }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
