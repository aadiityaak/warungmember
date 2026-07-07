<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
import { Head, Link } from '@inertiajs/vue3';
import { useCart } from '@/composables/useCart';

defineProps<{ title?: string }>();

const cart = useCart();
</script>

<template>
    <Head :title="title ?? 'Mas Mbull'" />
    <div class="flex min-h-screen flex-col bg-white">
        <!-- Header -->
        <header class="sticky top-0 z-10 border-b border-[#dadad3] bg-white">
            <div class="mx-auto flex max-w-md items-center justify-between px-4 py-3">
                <div class="flex items-center gap-2">
                    <img src="/logo/logo-mas-mbull.jpg" alt="Logo" class="h-8 w-auto object-contain" />
                    <span class="text-lg font-bold text-[#E22625]">Warung Mas Mbull</span>
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
                <Link :href="route('member.dashboard')" class="flex flex-col items-center gap-0.5 text-xs font-semibold text-[#000000] hover:text-[#E22625]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    Beranda
                </Link>
                <Link :href="route('member.points')" class="flex flex-col items-center gap-0.5 text-xs font-semibold text-[#000000] hover:text-[#E22625]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Poin
                </Link>
                <Link :href="route('member.products.index')" class="flex flex-col items-center gap-0.5 text-xs font-semibold text-[#000000] hover:text-[#E22625]">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21a1 1 0 0 0 1-1v-5.35c0-.457.316-.844.727-1.041a4 4 0 0 0-2.134-7.589 5 5 0 0 0-9.186 0 4 4 0 0 0-2.134 7.588c.411.198.727.585.727 1.041V20a1 1 0 0 0 1 1Z"/><path d="M6 17h12"/></svg>
                    Menu
                </Link>
                <Link :href="route('member.notifications')" class="flex flex-col items-center gap-0.5 text-xs font-semibold text-[#000000] hover:text-[#E22625]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    Notifikasi
                </Link>
                <Link :href="route('member.profile')" class="flex flex-col items-center gap-0.5 text-xs font-semibold text-[#000000] hover:text-[#E22625]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    Profil
                </Link>
            </div>
        </nav>
        <Toaster />
    </div>
</template>
