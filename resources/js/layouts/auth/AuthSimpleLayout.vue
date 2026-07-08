<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center bg-[#fbfbf9] p-4"
    >
        <!-- Card -->
        <div class="w-full max-w-[400px] rounded-[32px] bg-white px-8 py-10">
            <!-- Logo -->
            <div class="flex flex-col items-center gap-5">
                <Link :href="route('home')" class="flex flex-col items-center gap-2">
                    <img
                        src="/logo/logo-mas-mbull.jpg"
                        alt="Warung Mas Mbull"
                        class="h-12 w-auto object-contain"
                    />
                </Link>
                <div class="space-y-1 text-center">
                    <h1 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                        {{ title }}
                    </h1>
                    <p class="text-sm leading-[1.4] text-[#62625b]">
                        {{ description }}
                    </p>
                </div>
            </div>

            <div class="mt-8">
                <slot />
            </div>
        </div>

        <!-- Footer -->
        <p class="mt-8 text-xs leading-[1.4] text-[#91918c]">
            &copy; {{ new Date().getFullYear() }} Warung Mas Mbull
        </p>
    </div>
</template>
