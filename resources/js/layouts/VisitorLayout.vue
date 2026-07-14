<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Toaster } from '@/components/ui/sonner';

const page = usePage();
const branding = page.props.branding as Record<string, any>;
</script>

<template>
    <Head :title="branding?.app_name ?? 'Warung Mas Mbull'" />

    <div class="flex min-h-screen flex-col bg-white">
        <!-- Header -->
        <header class="sticky top-0 z-10 border-b border-[#dadad3] bg-white/95 backdrop-blur-sm">
            <div class="mx-auto flex max-w-md items-center justify-between px-4 py-3">
                <div class="flex items-center gap-2">
                    <img
                        v-if="branding?.logo_url"
                        :src="branding.logo_url"
                        :alt="branding.app_name"
                        class="h-8 w-auto object-contain"
                    />
                    <span class="text-lg font-bold text-[#000000]">{{ branding?.app_name ?? 'Warung Mas Mbull' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('login')"
                        class="rounded-full px-4 py-1.5 text-sm font-semibold text-[#000000] transition-colors hover:bg-[#f6f6f3]"
                    >
                        Masuk
                    </Link>
                    <Link
                        :href="route('register')"
                        class="rounded-full bg-[#E22625] px-4 py-1.5 text-sm font-semibold text-white transition-colors hover:bg-[#cc001f]"
                    >
                        Daftar
                    </Link>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="mx-auto w-full max-w-md flex-1 px-4 pt-4">
            <slot />
        </main>

        <!-- Footer — removed for Android app -->

        <Toaster />
    </div>
</template>
