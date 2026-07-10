<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Deposit' },
        ] as BreadcrumbItem[],
    },
});

const { members, memberOptions } = defineProps<{
    members: {
        data: Array<{
            id: number;
            user: { name: string; email: string };
            member_code: string;
            deposit_balance: number;
        }>;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
    };
    memberOptions: Array<{
        id: number;
        user: { name: string; email: string };
        member_code: string;
    }>;
}>();

const form = useForm({
    member_id: '',
    amount: undefined as number | undefined,
});

const page = usePage();

// Searchable combobox
const searchQuery = ref('');
const dropdownOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const filteredMembers = computed(() => {
    if (!searchQuery.value) return memberOptions;
    const q = searchQuery.value.toLowerCase();
    return memberOptions.filter(
        (m) =>
            m.user.name.toLowerCase().includes(q) ||
            m.member_code.toLowerCase().includes(q),
    );
});

const selectedMemberName = computed(() => {
    if (!form.member_id) return '';
    const m = memberOptions.find((m) => m.id === Number(form.member_id));
    return m ? `${m.user.name} — ${m.member_code}` : '';
});

function selectMember(id: number) {
    form.member_id = String(id);
    searchQuery.value = selectedMemberName.value;
    dropdownOpen.value = false;
}

function toggleDropdown() {
    if (!dropdownOpen.value) {
        searchQuery.value = '';
        dropdownOpen.value = true;
    } else {
        dropdownOpen.value = false;
    }
}

function onInputFocus() {
    searchQuery.value = '';
    dropdownOpen.value = true;
}

// Click outside
function onDocumentClick(e: MouseEvent) {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        dropdownOpen.value = false;
    }
}

// Track click outside
import { onMounted, onUnmounted } from 'vue';
onMounted(() => document.addEventListener('click', onDocumentClick));
onUnmounted(() => document.removeEventListener('click', onDocumentClick));

function submit() {
    form.post(route('admin.deposits.store'), {
        onSuccess: () => {
            form.reset();
            searchQuery.value = '';
        },
    });
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
    <Head title="Manajemen Deposit" />

    <div class="mx-6 pt-6">
        <!-- Header -->
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Manajemen Deposit
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Top-up saldo deposit member di WarungMember
            </p>
        </header>

        <!-- Top-up Form -->
        <div class="mb-6 rounded-2xl border border-[#dadad3] bg-white">
            <div class="overflow-hidden rounded-t-2xl border-b border-[#dadad3] px-5 py-4">
                <h3 class="text-base font-bold leading-[1.2] text-[#000000]">Top-up Deposit</h3>
            </div>
            <form @submit.prevent="submit" class="px-5 py-4">
                <div class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[200px] relative" ref="dropdownRef">
                        <label class="mb-1 block text-sm font-bold leading-[1.4] text-[#000000]">Member</label>
                        <input
                            :value="dropdownOpen ? searchQuery : selectedMemberName"
                            @focus="onInputFocus"
                            @input="(e) => { searchQuery = (e.target as HTMLInputElement).value; dropdownOpen = true; }"
                            :placeholder="form.member_id ? '' : 'Cari nama atau ID member...'"
                            class="w-full rounded-full border-0 bg-[#f6f6f3] px-4 py-2.5 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c8c8c1] cursor-pointer"
                        />
                        <!-- Dropdown -->
                        <div
                            v-if="dropdownOpen"
                            class="absolute z-50 mt-1 w-full rounded-xl border border-[#dadad3] bg-white shadow-lg max-h-60 overflow-y-auto"
                        >
                            <div
                                v-for="m in filteredMembers"
                                :key="m.id"
                                @click="selectMember(m.id)"
                                class="flex items-center justify-between px-4 py-2.5 text-sm cursor-pointer hover:bg-[#f6f6f3] transition-colors"
                                :class="{ 'bg-[#f6f6f3] font-semibold': Number(form.member_id) === m.id }"
                            >
                                <span class="text-[#000000]">{{ m.user.name }}</span>
                                <span class="text-xs font-mono text-[#91918c]">{{ m.member_code }}</span>
                            </div>
                            <div v-if="filteredMembers.length === 0" class="px-4 py-3 text-sm text-[#91918c] text-center">
                                Member tidak ditemukan
                            </div>
                        </div>
                        <p v-if="form.errors.member_id" class="mt-1 text-xs leading-[1.4] text-[#E22625]">{{ form.errors.member_id }}</p>
                    </div>
                    <div class="w-[180px]">
                        <label for="amount" class="mb-1 block text-sm font-bold leading-[1.4] text-[#000000]">Nominal</label>
                        <input
                            id="amount"
                            v-model="form.amount"
                            type="number"
                            min="1000"
                            placeholder="Rp 0"
                            class="w-full rounded-full border-0 bg-[#f6f6f3] px-4 py-2.5 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#c8c8c1]"
                        />
                        <p v-if="form.errors.amount" class="mt-1 text-xs leading-[1.4] text-[#E22625]">{{ form.errors.amount }}</p>
                    </div>
                    <div class="flex items-end">
                        <Button type="submit" :disabled="form.processing">Proses Deposit</Button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Empty state -->
        <div v-if="members.data.length === 0" class="rounded-2xl bg-[#f6f6f3] py-16 text-center">
            <p class="text-sm leading-[1.4] text-[#62625b]">Belum ada member terdaftar.</p>
        </div>

        <!-- Member Balance Table -->
        <div v-else class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white">
            <div class="border-b border-[#dadad3] px-5 py-4">
                <h3 class="text-base font-bold leading-[1.2] text-[#000000]">Saldo Member</h3>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#dadad3]">
                        <th class="px-5 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Nama</th>
                        <th class="px-5 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden sm:table-cell">Email</th>
                        <th class="px-5 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden md:table-cell">ID Member</th>
                        <th class="px-5 py-3 text-right text-sm font-bold leading-[1.4] text-[#000000]">Saldo</th>
                        <th class="px-5 py-3 text-right text-sm font-bold leading-[1.4] text-[#000000]"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="m in members.data"
                        :key="m.id"
                        class="border-b border-[#e5e5e0] last:border-0 transition-colors hover:bg-[#fbfbf9]"
                    >
                        <td class="px-5 py-3 text-sm leading-[1.4] font-semibold text-[#000000]">{{ m.user.name }}</td>
                        <td class="px-5 py-3 text-sm leading-[1.4] text-[#62625b] hidden sm:table-cell">{{ m.user.email }}</td>
                        <td class="px-5 py-3 text-sm leading-[1.4] font-mono font-semibold text-[#000000] hidden md:table-cell">{{ m.member_code }}</td>
                        <td class="px-5 py-3 text-sm leading-[1.4] font-semibold text-right" :class="m.deposit_balance > 0 ? 'text-[#E22625]' : 'text-[#91918c]'">
                            Rp {{ m.deposit_balance.toLocaleString('id-ID') }}
                        </td>
                        <td class="px-5 py-3 text-right">
                            <Link :href="route('admin.deposits.history', m.id)" class="inline-flex items-center gap-1 text-sm font-semibold text-[#62625b] hover:text-[#000000] transition-colors">
                                Riwayat
                            </Link>
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
                    <template v-for="(p, idx) in paginationPages" :key="idx">
                        <span
                            v-if="p === '...'"
                            class="inline-flex h-9 w-9 items-center justify-center text-sm font-bold leading-[1] text-[#62625b]"
                        >
                            ...
                        </span>
                        <Link
                            v-else
                            :href="route('admin.deposits.index', { page: p })"
                            :class="[
                                'inline-flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold leading-[1] transition-colors',
                                p === members.current_page
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
