// Welcome.vue — visitor landing page
<script setup lang="ts">
import VisitorLayout from '@/layouts/VisitorLayout.vue';
import BannerCarousel from '@/components/BannerCarousel.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineOptions({ layout: VisitorLayout });

const page = usePage();
const featuredProducts = (page.props.featuredProducts ?? []) as Array<{
    id: number;
    name: string;
    price: number;
    current_price: number;
    image: string | null;
    is_on_discount: boolean;
}>;
const stats = (page.props.stats ?? { total_members: 0, total_outlets: 0 }) as {
    total_members: number;
    total_outlets: number;
};

const banners = ['/banner/1.jpg', '/banner/2.jpg', '/banner/3.jpg'];

const testimonials = [
    {
        name: 'Siti Rahma',
        text: 'Awalnya ragu daftar, tapi setelah jadi member poinnya cepat terkumpul. Sekarang bisa tukar reward tiap bulan!',
        rating: 5,
    },
    {
        name: 'Budi Santoso',
        text: 'Deposit saldo bikin bayar makin praktis. Nggak perlu bawa uang tunai ke warung.',
        rating: 5,
    },
    {
        name: 'Dewi Lestari',
        text: 'Notifikasi promo-nya selalu up-to-date. Sering dapat voucher diskon spesial member.',
        rating: 4,
    },
];

const steps = [
    {
        icon: '1',
        title: 'Daftar Gratis',
        desc: 'Buat akun dalam 1 menit. Cukup isi nomor WA dan nama.',
    },
    {
        icon: '2',
        title: 'Belanja di Outlet',
        desc: 'Tunjukkan nomor member saat bayar di outlet terdekat.',
    },
    {
        icon: '3',
        title: 'Kumpulkan Poin',
        desc: 'Poin otomatis bertambah. Tukarkan dengan reward & voucher menarik!',
    },
];

const benefits = [
    'Poin bertambah setiap transaksi',
    'Tukarkan poin dengan reward eksklusif',
    'Voucher diskon spesial member',
    'Top up saldo deposit kapan saja',
];

function formatRupiah(n: number): string {
    return 'Rp ' + n.toLocaleString('id-ID');
}

// PWA install
const installPrompt = ref<Event | null>(null);
const showInstallBanner = ref(false);

onMounted(() => {
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        installPrompt.value = e;
        showInstallBanner.value = true;
    });
});

async function installApp() {
    if (!installPrompt.value) return;
    (installPrompt.value as any).prompt();
    const result = await (installPrompt.value as any).userChoice;
    if (result.outcome === 'accepted') {
        showInstallBanner.value = false;
    }
    installPrompt.value = null;
}

function dismissInstall() {
    showInstallBanner.value = false;
}
</script>

<template>
    <Head title="Warung Mas Mbull" />

    <div class="flex flex-col gap-6">
        <!-- PWA Install Banner -->
        <div
            v-if="showInstallBanner"
            class="flex items-center gap-3 rounded-2xl bg-gradient-to-r from-[#E22625] to-[#cc001f] px-4 py-3 text-white"
        >
            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                    />
                </svg>
            </div>
            <div class="flex-1 text-sm leading-[1.3]">
                <p class="font-semibold">Install Aplikasi</p>
                <p class="text-white/80">
                    Akses lebih cepat lewat layar utama.
                </p>
            </div>
            <button
                @click="installApp"
                class="rounded-full bg-white px-4 py-1.5 text-xs font-bold text-[#E22625] transition-colors hover:bg-[#f6f6f3]"
            >
                Install
            </button>
            <button
                @click="dismissInstall"
                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-white/70 hover:bg-white/10 hover:text-white"
                aria-label="Tutup"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <!-- Hero Section -->
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p
                    class="text-[11px] font-semibold tracking-widest text-[#E22625] uppercase"
                >
                    Program Loyalitas
                </p>
                <h2
                    class="mt-1 text-[22px] leading-[1.25] font-semibold text-[#000000]"
                >
                    Halo, Selamat Datang!
                </h2>
                <p class="mt-1 text-sm leading-[1.4] text-[#62625b]">
                    Nikmati program loyalitas dan promo spesial dari kami.
                </p>
            </div>
            <Link
                :href="route('login')"
                class="inline-flex shrink-0 items-center gap-1 rounded-full bg-[#f6f6f3] px-3 py-1.5 text-xs font-semibold text-[#000000] transition-colors hover:bg-[#000000] hover:text-white"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                </svg>
                Cari Outlet
            </Link>
        </div>

        <!-- Banner Carousel -->
        <BannerCarousel :images="banners" alt-prefix="Banner" />

        <!-- Stats / Social Proof -->
        <div
            v-if="stats.total_members > 0 || stats.total_outlets > 0"
            class="grid grid-cols-2 gap-2"
        >
            <div
                v-if="stats.total_members > 0"
                class="flex items-center gap-3 rounded-2xl bg-[#f6f6f3] px-4 py-4"
            >
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white"
                >
                    <svg
                        class="h-5 w-5 text-[#E22625]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
                        />
                    </svg>
                </div>
                <div>
                    <p
                        class="text-[20px] leading-[1.2] font-bold text-[#000000]"
                    >
                        {{ stats.total_members.toLocaleString('id-ID') }}
                    </p>
                    <p class="text-xs text-[#62625b]">Member Aktif</p>
                </div>
            </div>
            <div
                v-if="stats.total_outlets > 0"
                class="flex items-center gap-3 rounded-2xl bg-[#f6f6f3] px-4 py-4"
            >
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white"
                >
                    <svg
                        class="h-5 w-5 text-[#E22625]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                    </svg>
                </div>
                <div>
                    <p
                        class="text-[20px] leading-[1.2] font-bold text-[#000000]"
                    >
                        {{ stats.total_outlets.toLocaleString('id-ID') }}
                    </p>
                    <p class="text-xs text-[#62625b]">Outlet Tersedia</p>
                </div>
            </div>
        </div>

        <!-- How It Works -->
        <div>
            <h3 class="text-lg leading-[1.3] font-semibold text-[#000000]">
                Cara Kerja
            </h3>
            <div class="mt-3 flex flex-col gap-3">
                <div
                    v-for="(step, i) in steps"
                    :key="i"
                    class="flex items-start gap-3 rounded-2xl bg-[#f6f6f3] px-4 py-4"
                >
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#E22625] text-sm font-bold text-white"
                    >
                        {{ step.icon }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[#000000]">
                            {{ step.title }}
                        </p>
                        <p class="mt-0.5 text-xs text-[#62625b]">
                            {{ step.desc }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Featured Products -->
        <div v-if="featuredProducts.length">
            <h3 class="text-lg leading-[1.3] font-semibold text-[#000000]">
                Menu Favorit
            </h3>
            <p class="mt-0.5 text-xs text-[#62625b]">
                Lihat menu andalan kami.
            </p>
            <div class="mt-3 grid grid-cols-2 gap-3">
                <div
                    v-for="product in featuredProducts"
                    :key="product.id"
                    class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white"
                >
                    <div
                        class="relative aspect-square overflow-hidden bg-[#f6f6f3]"
                    >
                        <img
                            v-if="product.image"
                            :src="product.image"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center"
                        >
                            <svg
                                class="h-10 w-10 text-[#dadad3]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                        <div
                            v-if="product.is_on_discount"
                            class="absolute top-2 left-2 rounded-full bg-[#E22625] px-2 py-0.5 text-[10px] font-bold text-white"
                        >
                            DISKON
                        </div>
                    </div>
                    <div class="p-3">
                        <h4
                            class="line-clamp-2 text-sm leading-[1.3] font-semibold text-[#000000]"
                        >
                            {{ product.name }}
                        </h4>
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="text-sm font-bold text-[#E22625]">{{
                                formatRupiah(product.current_price)
                            }}</span>
                            <span
                                v-if="product.is_on_discount"
                                class="text-xs text-[#91918c] line-through"
                            >
                                {{ formatRupiah(product.price) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <Link
                :href="route('login')"
                class="mt-3 flex items-center justify-center gap-1 rounded-full bg-[#f6f6f3] py-2.5 text-sm font-semibold text-[#000000] transition-colors hover:bg-[#000000] hover:text-white"
            >
                Lihat Menu Lengkap
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                    />
                </svg>
            </Link>
        </div>

        <!-- Testimonials -->
        <div>
            <h3 class="text-lg leading-[1.3] font-semibold text-[#000000]">
                Kata Member
            </h3>
            <p class="mt-0.5 text-xs text-[#62625b]">
                Apa kata mereka setelah bergabung.
            </p>
            <div class="mt-3 flex flex-col gap-3">
                <div
                    v-for="(t, i) in testimonials"
                    :key="i"
                    class="rounded-2xl bg-[#f6f6f3] px-4 py-4"
                >
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-[#E22625] text-xs font-bold text-white"
                        >
                            {{ t.name.charAt(0) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-[#000000]">
                                {{ t.name }}
                            </p>
                            <div class="flex items-center gap-0.5">
                                <svg
                                    v-for="s in t.rating"
                                    :key="s"
                                    class="h-3 w-3 text-[#E22625]"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-sm leading-[1.5] text-[#62625b] italic">
                        "{{ t.text }}"
                    </p>
                </div>
            </div>
        </div>

        <!-- Fitur WarungMember -->
        <div>
            <h3 class="text-lg leading-[1.3] font-semibold text-[#000000]">
                Fitur WarungMember
            </h3>
            <div class="mt-3 grid grid-cols-2 gap-2">
                <div class="rounded-2xl bg-[#f6f6f3] px-4 py-4">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-white"
                    >
                        <svg
                            class="h-5 w-5 text-[#E22625]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-[#000000]">
                        Kumpulkan Poin
                    </p>
                    <p class="mt-0.5 text-xs text-[#62625b]">
                        Poin bertambah tiap transaksi.
                    </p>
                </div>
                <div class="rounded-2xl bg-[#f6f6f3] px-4 py-4">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-white"
                    >
                        <svg
                            class="h-5 w-5 text-[#E22625]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"
                            />
                        </svg>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-[#000000]">
                        Tukar Reward
                    </p>
                    <p class="mt-0.5 text-xs text-[#62625b]">
                        Tukarkan poin dengan hadiah.
                    </p>
                </div>
                <div class="rounded-2xl bg-[#f6f6f3] px-4 py-4">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-white"
                    >
                        <svg
                            class="h-5 w-5 text-[#E22625]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                            />
                        </svg>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-[#000000]">
                        Isi Deposit
                    </p>
                    <p class="mt-0.5 text-xs text-[#62625b]">
                        Top up saldo kapan saja.
                    </p>
                </div>
                <div class="rounded-2xl bg-[#f6f6f3] px-4 py-4">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-white"
                    >
                        <svg
                            class="h-5 w-5 text-[#E22625]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                            />
                        </svg>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-[#000000]">
                        Notifikasi
                    </p>
                    <p class="mt-0.5 text-xs text-[#62625b]">
                        Update promo & pesanan.
                    </p>
                </div>
            </div>
        </div>

        <!-- Bottom CTA — full-width anchor -->
        <div class="-mx-4 mt-2 bg-gradient-to-b from-[#e60023] to-[#b3001a] px-5 pb-8 pt-7 text-white">
            <!-- Decorative top border -->
            <div
                class="-mx-5 -mt-7 mb-5 h-5 bg-gradient-to-b from-white/[0.06] to-transparent"
            ></div>

            <div class="mx-auto max-w-md">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/15"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                            />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-[11px] font-semibold tracking-widest text-white/60 uppercase"
                        >
                            WarungMember
                        </p>
                        <h3 class="text-xl leading-[1.25] font-bold">
                            Siap Jadi Member?
                        </h3>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-1.5">
                    <span
                        v-for="(b, i) in benefits"
                        :key="i"
                        class="inline-flex items-center gap-1 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/90"
                    >
                        <svg
                            class="h-3 w-3 shrink-0 text-white/70"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                        {{ b }}
                    </span>
                </div>

                <div class="mt-5 flex gap-2">
                    <Link
                        :href="route('register')"
                        class="flex-1 rounded-xl bg-white py-3 text-center text-sm font-bold text-[#e60023] shadow-lg shadow-black/15 transition-all active:scale-[0.97]"
                    >
                        Daftar Gratis
                    </Link>
                    <Link
                        :href="route('login')"
                        class="flex-1 rounded-xl border border-white/25 py-3 text-center text-sm font-semibold text-white transition-colors hover:bg-white/10 active:scale-[0.97]"
                    >
                        Login
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
