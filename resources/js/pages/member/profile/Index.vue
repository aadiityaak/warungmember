<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import JsBarcode from 'jsbarcode';
import MemberLayout from '@/layouts/MemberLayout.vue';

defineOptions({
    layout: MemberLayout,
});

const page = usePage();
const { profile } = defineProps<{
    profile: {
        id: number;
        name: string;
        email: string;
        avatar: string | null;
        member_code: string | null;
        birth_date: string | null;
        total_points: number;
        deposit_balance: number;
    };
}>();

interface MenuItem {
    title: string;
    subtitle?: string;
    icon: string;
    href: string;
    external?: boolean;
    method?: 'get' | 'post' | 'delete';
}

const menuItems: MenuItem[] = [
    {
        title: 'Edit Profil',
        subtitle: 'Nama, email, foto, password',
        icon: 'user',
        href: route('member.profile.edit'),
    },
    {
        title: 'Riwayat Order',
        subtitle: 'Lihat riwayat order kamu',
        icon: 'history',
        href: route('member.orders.history'),
    },
    {
        title: 'Chat WhatsApp CS',
        subtitle: 'Hubungi customer service',
        icon: 'chat',
        href: 'https://wa.me/' + (page.props.branding?.whatsapp_number ?? '6281335405231').replace(/^0/, '62') + '?text=Halo%20CS%20WarungMember',
        external: true,
    },
    {
        title: 'Syarat & Ketentuan',
        subtitle: 'Baca aturan program loyalitas',
        icon: 'file',
        href: route('member.terms'),
    },
    {
        title: 'Kebijakan Privasi',
        subtitle: 'Cara kami menjaga data kamu',
        icon: 'shield',
        href: route('member.privacy'),
    },
    {
        title: 'Tentang WarungMember',
        subtitle: 'Versi 1.0.0',
        icon: 'info',
        href: '#',
    },
];

function openMenuItem(item: MenuItem) {
    if (item.external) {
        window.open(item.href, '_blank');
    } else if (item.method === 'post') {
        router.post(item.href);
    } else {
        router.get(item.href);
    }
}

function logout() {
    router.post(route('logout'));
}

function formatRupiah(n: number): string {
    return 'Rp ' + n.toLocaleString('id-ID');
}

const barcodeRef = ref<HTMLOrSVGImageElement | null>(null);

onMounted(() => {
    if (profile.member_code && barcodeRef.value) {
        JsBarcode(barcodeRef.value, profile.member_code, {
            format: 'CODE128',
            width: 1.5,
            height: 50,
            displayValue: false,
            margin: 0,
            background: '#ffffff',
            lineColor: '#000000',
        });
    }
});
</script>

<template>
    <Head title="Profil" />

    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div>
            <h2 class="text-[22px] font-semibold leading-[1.25] text-[#000000]">
                Profil Saya
            </h2>
            <p class="mt-1 text-sm leading-[1.4] text-[#62625b]">
                Kelola akun & pengaturan kamu
            </p>
        </div>

        <!-- User Card -->
        <div class="flex items-center gap-4 rounded-2xl bg-[#f6f6f3] px-4 py-4">
            <img
                v-if="profile.avatar"
                :src="profile.avatar"
                alt="Avatar"
                class="h-14 w-14 rounded-full border-2 border-[#dadad3] object-cover"
            />
            <div
                v-else
                class="flex h-14 w-14 items-center justify-center rounded-full bg-white"
            >
                <svg class="h-7 w-7 text-[#91918c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-lg font-semibold leading-[1.3] text-[#000000] truncate">{{ profile.name }}</p>
                <p class="text-sm leading-[1.4] text-[#62625b] truncate">{{ profile.email }}</p>
                <p v-if="profile.member_code" class="mt-0.5 text-xs font-semibold leading-[1.4] text-[#91918c]">{{ profile.member_code }}</p>
            </div>
        </div>

        <!-- Member Card dengan Barcode -->
        <div v-if="profile.member_code" class="rounded-2xl border border-[#dadad3] bg-white overflow-hidden">
            <div class="flex items-center justify-between px-4 pt-4 pb-2">
                <div>
                    <p class="text-xs leading-[1.4] text-[#91918c]">Kartu Member</p>
                    <p class="mt-0.5 text-lg font-bold leading-[1.3] text-[#000000] font-mono tracking-wider">{{ profile.member_code }}</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#f6f6f3]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-scan-barcode-icon lucide-scan-barcode"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M8 7v10"/><path d="M12 7v10"/><path d="M17 7v10"/></svg>
                </div>
            </div>
            <div class="bg-white px-4 pb-4 flex justify-center">
                <img ref="barcodeRef" alt="Barcode Member" />
            </div>
        </div>

        <!-- Stats -->
        <div class="flex gap-2">
            <Link
                :href="route('member.points')"
                class="flex-1 rounded-2xl bg-[#f6f6f3] px-4 py-3 text-center transition-all hover:bg-[#000000] hover:text-white group"
            >
                <p class="text-xs leading-[1.4] text-[#91918c] group-hover:text-white/70">Total Poin</p>
                <p class="mt-0.5 text-lg font-semibold leading-[1.3] text-[#000000] group-hover:text-white">{{ profile.total_points.toLocaleString('id-ID') }}</p>
                <p class="mt-0.5 text-[10px] font-medium text-[#91918c] group-hover:text-white/50">Lihat Riwayat →</p>
            </Link>
            <Link
                :href="route('member.deposits')"
                class="flex-1 rounded-2xl bg-[#f6f6f3] px-4 py-3 text-center transition-all hover:bg-[#000000] hover:text-white group"
            >
                <p class="text-xs leading-[1.4] text-[#91918c] group-hover:text-white/70">Saldo Deposit</p>
                <p class="mt-0.5 text-lg font-semibold leading-[1.3] text-[#000000] group-hover:text-white">{{ formatRupiah(profile.deposit_balance) }}</p>
                <p class="mt-0.5 text-[10px] font-medium text-[#91918c] group-hover:text-white/50">Lihat Riwayat →</p>
            </Link>
        </div>

        <!-- Menu List -->
        <div class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white">
            <button
                v-for="(item, idx) in menuItems"
                :key="item.title"
                @click="openMenuItem(item)"
                class="flex w-full items-center gap-3 px-4 py-3.5 text-left transition-colors hover:bg-[#fbfbf9]"
                :class="{ 'border-t border-[#e5e5e0]': idx > 0 }"
            >
                <!-- Icon -->
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f6f6f3]">
                    <!-- User -->
                    <svg v-if="item.icon === 'user'" class="h-4 w-4 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    <!-- History -->
                    <svg v-else-if="item.icon === 'history'" class="h-4 w-4 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <!-- Chat -->
                    <svg v-else-if="item.icon === 'chat'" class="h-4 w-4 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    <!-- File -->
                    <svg v-else-if="item.icon === 'file'" class="h-4 w-4 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <!-- Shield -->
                    <svg v-else-if="item.icon === 'shield'" class="h-4 w-4 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    <!-- Info -->
                    <svg v-else-if="item.icon === 'info'" class="h-4 w-4 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold leading-[1.4] text-[#000000]">{{ item.title }}</p>
                    <p v-if="item.subtitle" class="text-xs leading-[1.4] text-[#62625b]">{{ item.subtitle }}</p>
                </div>
                <svg class="h-4 w-4 shrink-0 text-[#91918c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>

        <!-- Logout -->
        <button
            @click="logout"
            class="w-full rounded-2xl border border-[#dadad3] bg-white px-4 py-3 text-center text-sm font-semibold leading-[1.4] text-[#e60023] transition-colors hover:bg-[#e60023] hover:text-white"
        >
            Keluar
        </button>
    </div>
</template>
