<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useCart } from '@/composables/useCart';
import { usePushNotification } from '@/composables/usePushNotification';
import { computed, onMounted } from 'vue';

defineProps<{ title?: string }>();

const cart = useCart();
const page = usePage();

const unreadCount = computed(() => (page.props.unreadNotifications as number) ?? 0);

function isActive(...names: string[]): boolean {
    return names.some((name) => route().current(name));
}

onMounted(() => {
    cart.loadFromServer();
    usePushNotification().init();
});
</script>

<template>
    <Head :title="title ?? 'Mas Mbull'" />
    <div class="flex min-h-screen flex-col bg-white">
        <!-- Header -->
        <header class="sticky top-0 z-10 border-b border-[#dadad3] bg-white">
            <div class="mx-auto flex max-w-md items-center justify-between px-4 py-3">
                <div class="flex items-center gap-2">
                    <img
                        v-if="$page.props.branding.logo_url"
                        :src="$page.props.branding.logo_url"
                        :alt="$page.props.branding.app_name"
                        class="h-8 w-auto object-contain"
                    />
                    <span class="text-lg font-bold text-[#000000]">{{ $page.props.branding.app_name }}</span>
                </div>
                <Link
                    :href="route('member.orders.index')"
                    class="relative inline-flex h-9 w-9 items-center justify-center rounded-full text-[#000000] hover:bg-[#f6f6f3]"
                >
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    <span
                        v-if="cart.totalItems() > 0"
                        class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-[#E22625] px-1 text-[10px] font-bold text-white"
                    >
                        {{ cart.totalItems() }}
                    </span>
                </Link>
                <slot name="header-actions" />
            </div>
        </header>

        <!-- Content -->
        <main class="mx-auto w-full max-w-md flex-1 px-4 py-4">
            <slot />
        </main>

        <!-- Bottom Nav -->
        <nav class="sticky bottom-0 z-10 border-t border-[#dadad3] bg-white">
            <div class="mx-auto flex max-w-md justify-around py-2">
                <Link :href="route('member.dashboard')" :class="['flex flex-col items-center gap-0.5 text-xs font-semibold', isActive('member.dashboard') ? 'text-[#E22625]' : 'text-[#000000] hover:text-[#E22625]']">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    Beranda
                </Link>
                <Link :href="route('member.points')" :class="['flex flex-col items-center gap-0.5 text-xs font-semibold', isActive('member.points') ? 'text-[#E22625]' : 'text-[#000000] hover:text-[#E22625]']">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
                    Poin
                </Link>
                <Link :href="route('member.products.index')" :class="['flex flex-col items-center gap-0.5 text-xs font-semibold', isActive('member.products.*') ? 'text-[#E22625]' : 'text-[#000000] hover:text-[#E22625]']">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21a1 1 0 0 0 1-1v-5.35c0-.457.316-.844.727-1.041a4 4 0 0 0-2.134-7.589 5 5 0 0 0-9.186 0 4 4 0 0 0-2.134 7.588c.411.198.727.585.727 1.041V20a1 1 0 0 0 1 1Z"/><path d="M6 17h12"/></svg>
                    Menu
                </Link>
                <Link :href="route('member.notifications')" :class="['relative flex flex-col items-center gap-0.5 text-xs font-semibold', isActive('member.notifications') ? 'text-[#E22625]' : 'text-[#000000] hover:text-[#E22625]']">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.268 21a2 2 0 0 0 3.464 0"/><path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"/></svg>
                    <span
                        v-if="unreadCount > 0"
                        class="absolute -right-1 -top-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-[#e60023] px-1 text-[10px] font-bold text-white"
                    >
                        {{ unreadCount > 99 ? '99+' : unreadCount }}
                    </span>
                    Notifikasi
                </Link>
                <Link :href="route('member.profile')" :class="['flex flex-col items-center gap-0.5 text-xs font-semibold', isActive('member.profile') ? 'text-[#E22625]' : 'text-[#000000] hover:text-[#E22625]']">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                    Profil
                </Link>
            </div>
        </nav>
        <Toaster />
    </div>
</template>
