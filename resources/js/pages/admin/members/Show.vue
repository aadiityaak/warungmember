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

const { member, depositHistory, pointHistory, pushSubscription } = defineProps<{
    member: {
        id: number;
        name: string;
        email: string;
        avatar: string | null;
        created_at: string;
        member?: {
            id: number;
            member_code: string;
            total_points: number;
            deposit_balance: number;
            birth_date: string | null;
        } | null;
    };
    depositHistory: {
        data: Array<{
            id: number;
            type: 'topup' | 'payment' | 'refund';
            amount: number;
            note: string | null;
            created_at: string;
        }>;
    } | null;
    pointHistory: {
        data: Array<{
            id: number;
            type: 'earn' | 'redeem' | 'expire';
            amount: number;
            note: string | null;
            created_at: string;
        }>;
    } | null;
    pushSubscription: {
        fcm_token: string | null;
        platform: string | null;
        subscribed: boolean;
        created_at: string | null;
    } | null;
}>();

const depositTypeLabel: Record<string, string> = {
    topup: 'Top Up',
    payment: 'Pembayaran',
    refund: 'Refund',
};

const depositTypeClass: Record<string, string> = {
    topup: 'text-green-600',
    payment: 'text-red-600',
    refund: 'text-blue-600',
};

const depositTypeSign: Record<string, string> = {
    topup: '+',
    payment: '-',
    refund: '+',
};

const pointTypeLabel: Record<string, string> = {
    earn: 'Earn',
    redeem: 'Redeem',
    expire: 'Expire',
};

const pointTypeClass: Record<string, string> = {
    earn: 'text-green-600',
    redeem: 'text-red-600',
    expire: 'text-red-500',
};

const pointTypeSign: Record<string, string> = {
    earn: '+',
    redeem: '-',
    expire: '-',
};

function formatDateTime(date: string): string {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
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

        <!-- Info Cards -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-[#dadad3] bg-white p-6">
                <span class="text-xs font-medium leading-[1.5] text-[#91918c] uppercase tracking-wider">Akun</span>
                <div class="mt-3 space-y-3">
                    <div v-if="member.avatar" class="flex justify-center mb-2">
                        <img
                            :src="member.avatar"
                            :alt="member.name"
                            class="h-20 w-20 rounded-full object-cover border-2 border-[#dadad3]"
                        />
                    </div>
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

            <div class="rounded-2xl bg-[#f6f6f3] p-6">
                <span class="text-xs font-medium leading-[1.5] text-[#91918c] uppercase tracking-wider">Total Poin</span>
                <p class="mt-3 text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    {{ member.member ? member.member.total_points.toLocaleString('id-ID') : '0' }}
                </p>
            </div>

            <div class="rounded-2xl bg-[#f6f6f3] p-6">
                <span class="text-xs font-medium leading-[1.5] text-[#91918c] uppercase tracking-wider">Saldo Deposit</span>
                <p class="mt-3 text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    Rp {{ member.member ? member.member.deposit_balance.toLocaleString('id-ID') : '0' }}
                </p>
            </div>

            <div class="rounded-2xl bg-[#f6f6f3] p-6">
                <span class="text-xs font-medium leading-[1.5] text-[#91918c] uppercase tracking-wider">Tanggal Lahir</span>
                <p class="mt-3 text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                    {{ member.member?.birth_date
                        ? new Date(member.member.birth_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
                        : '-' }}
                </p>
            </div>
        </div>

        <!-- Push Notification Card -->
        <div class="mt-6">
            <div class="rounded-2xl border border-[#dadad3] bg-white p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-xs font-medium leading-[1.5] text-[#91918c] uppercase tracking-wider">Push Notification</span>
                    <span v-if="pushSubscription?.subscribed" class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">Aktif</span>
                    <span v-else class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-600">Tidak Aktif</span>
                </div>
                <div v-if="pushSubscription?.fcm_token" class="space-y-3">
                    <div>
                        <p class="text-xs leading-[1.5] text-[#91918c]">FCM Token</p>
                        <p class="mt-0.5 text-xs font-mono text-[#000000] break-all select-all">
                            {{ pushSubscription.fcm_token }}
                        </p>
                    </div>
                    <div class="flex gap-6">
                        <div>
                            <p class="text-xs leading-[1.5] text-[#91918c]">Platform</p>
                            <p class="mt-0.5 text-sm font-semibold text-[#000000]">{{ pushSubscription.platform ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs leading-[1.5] text-[#91918c]">Subscribe Sejak</p>
                            <p class="mt-0.5 text-sm font-semibold text-[#000000]">
                                {{ pushSubscription.created_at ? new Date(pushSubscription.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-' }}
                            </p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-[#62625b]">Belum ada perangkat yang terdaftar untuk push notification.</p>
            </div>
        </div>

        <!-- Empty State if no member data -->
        <div v-if="!member.member" class="mt-6 rounded-2xl bg-[#f6f6f3] px-8 py-12 text-center">
            <p class="text-sm leading-[1.4] text-[#62625b]">Data loyalitas belum tersedia untuk member ini.</p>
        </div>

        <!-- Riwayat Deposit -->
        <div v-if="depositHistory && depositHistory.data.length > 0" class="mt-6">
            <h3 class="mb-3 text-lg font-bold">Riwayat Deposit</h3>
            <div class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-[#e5e5e0] text-xs font-medium text-[#91918c] uppercase tracking-wider">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="t in depositHistory.data" :key="t.id" class="border-b border-[#e5e5e0] last:border-0">
                            <td class="px-4 py-3 whitespace-nowrap">{{ formatDateTime(t.created_at) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span :class="depositTypeClass[t.type] ?? ''">
                                    {{ depositTypeLabel[t.type] ?? t.type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right font-semibold"
                                :class="depositTypeClass[t.type] ?? ''">
                                {{ depositTypeSign[t.type] ?? '' }} Rp {{ t.amount.toLocaleString('id-ID') }}
                            </td>
                            <td class="px-4 py-3 text-[#62625b]">{{ t.note ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Riwayat Poin -->
        <div v-if="pointHistory && pointHistory.data.length > 0" class="mt-6">
            <h3 class="mb-3 text-lg font-bold">Riwayat Poin</h3>
            <div class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-[#e5e5e0] text-xs font-medium text-[#91918c] uppercase tracking-wider">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="t in pointHistory.data" :key="t.id" class="border-b border-[#e5e5e0] last:border-0">
                            <td class="px-4 py-3 whitespace-nowrap">{{ formatDateTime(t.created_at) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span :class="pointTypeClass[t.type] ?? ''">
                                    {{ pointTypeLabel[t.type] ?? t.type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right font-semibold"
                                :class="pointTypeClass[t.type] ?? ''">
                                {{ pointTypeSign[t.type] ?? '' }} {{ t.amount.toLocaleString('id-ID') }}
                            </td>
                            <td class="px-4 py-3 text-[#62625b]">{{ t.note ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
