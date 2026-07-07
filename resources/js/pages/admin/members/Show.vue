<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Member', href: route('admin.members.index') },
            { title: 'Detail' },
        ] as BreadcrumbItem[],
    },
});

const { member } = defineProps<{
    member: {
        id: number;
        name: string;
        email: string;
        created_at: string;
        member?: {
            id: number;
            member_code: string;
            total_points: number;
            deposit_balance: number;
            birth_date: string | null;
        } | null;
    };
}>();
</script>

<template>
    <Head :title="`Detail ${member.name}`" />

    <div class="mx-6 pt-6">
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                {{ member.name }}
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Detail member
            </p>
        </header>

        <!-- Member Code Badge -->
        <div v-if="member.member?.member_code" class="mb-6">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#f6f6f3] px-4 py-1.5 text-sm font-semibold leading-[1.4] text-[#000000]">
                <span class="h-2 w-2 rounded-full bg-[#e60023]"></span>
                {{ member.member.member_code }}
            </span>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <!-- Info Akun -->
            <div class="rounded-2xl border border-[#dadad3] bg-white p-6">
                <span class="text-xs font-medium leading-[1.5] text-[#91918c] uppercase tracking-wider">Akun</span>
                <div class="mt-3 space-y-3">
                    <div>
                        <p class="text-xs leading-[1.5] text-[#91918c]">Nama</p>
                        <p class="text-sm font-semibold leading-[1.4] text-[#000000]">{{ member.name }}</p>
                    </div>
                    <div>
                        <p class="text-xs leading-[1.5] text-[#91918c]">Email</p>
                        <p class="text-sm font-semibold leading-[1.4] text-[#000000]">{{ member.email }}</p>
                    </div>
                    <div>
                        <p class="text-xs leading-[1.5] text-[#91918c]">Terdaftar</p>
                        <p class="text-sm font-semibold leading-[1.4] text-[#000000]">
                            {{ new Date(member.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Total Poin -->
            <div class="rounded-2xl bg-[#f6f6f3] p-6">
                <span class="text-xs font-medium leading-[1.5] text-[#91918c] uppercase tracking-wider">Total Poin</span>
                <p class="mt-3 text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    {{ member.member ? member.member.total_points.toLocaleString('id-ID') : '0' }}
                </p>
            </div>

            <!-- Saldo Deposit -->
            <div class="rounded-2xl bg-[#f6f6f3] p-6">
                <span class="text-xs font-medium leading-[1.5] text-[#91918c] uppercase tracking-wider">Saldo Deposit</span>
                <p class="mt-3 text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    Rp {{ member.member ? member.member.deposit_balance.toLocaleString('id-ID') : '0' }}
                </p>
            </div>

            <!-- Tgl Lahir -->
            <div class="rounded-2xl bg-[#f6f6f3] p-6">
                <span class="text-xs font-medium leading-[1.5] text-[#91918c] uppercase tracking-wider">Tanggal Lahir</span>
                <p class="mt-3 text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    {{ member.member?.birth_date
                        ? new Date(member.member.birth_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
                        : '-' }}
                </p>
            </div>
        </div>

        <!-- Empty State if no member data -->
        <div v-if="!member.member" class="mt-6 rounded-2xl bg-[#f6f6f3] px-8 py-12 text-center">
            <p class="text-sm leading-[1.4] text-[#62625b]">Data loyalitas belum tersedia untuk member ini.</p>
        </div>
    </div>
</template>
