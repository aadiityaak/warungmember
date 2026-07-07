<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{ title?: string }>();
</script>

<template>
    <Head :title="title ?? 'WarungMember'" />
    <div class="mx-auto flex min-h-screen max-w-md flex-col bg-[#fbfbf9] shadow-lg">
        <!-- Header -->
        <header class="sticky top-0 z-10 flex items-center justify-between border-b border-[#dadad3] bg-white px-4 py-3">
            <h1 class="text-lg font-bold text-orange-600">WarungMember</h1>
            <slot name="header-actions" />
        </header>

        <!-- Content -->
        <main class="flex-1 px-4 py-4">
            <slot />
        </main>

        <!-- Bottom Nav -->
        <nav class="sticky bottom-0 z-10 border-t border-[#dadad3] bg-white">
            <div class="flex justify-around py-2">
                <Link :href="route('member.dashboard')" class="flex flex-col items-center gap-0.5 text-xs font-semibold text-[#000000] hover:text-orange-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    Beranda
                </Link>
                <Link :href="route('member.points')" class="flex flex-col items-center gap-0.5 text-xs font-semibold text-[#000000] hover:text-orange-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Poin
                </Link>
                <Link :href="route('member.rewards')" class="flex flex-col items-center gap-0.5 text-xs font-semibold text-[#000000] hover:text-orange-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                    Voucher
                </Link>
                <Link :href="route('member.notifications')" class="flex flex-col items-center gap-0.5 text-xs font-semibold text-[#000000] hover:text-orange-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    Notifikasi
                </Link>
                <Link :href="route('member.profile')" class="flex flex-col items-center gap-0.5 text-xs font-semibold text-[#000000] hover:text-orange-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    Profil
                </Link>
            </div>
        </nav>
        <Toaster />
    </div>
</template>
