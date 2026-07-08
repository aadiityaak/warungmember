<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';

defineOptions({ layout: MemberLayout });

const { outlet } = defineProps<{
    outlet: {
        id: number;
        name: string;
        address: string | null;
        phone: string | null;
        gallery: string[] | null;
        is_active: boolean;
        kasir: { name: string } | null;
    };
}>();

const currentSlide = ref(0);
const gallery = computed(() => outlet.gallery?.length ? outlet.gallery : []);

function prevSlide() {
    currentSlide.value = currentSlide.value === 0 ? gallery.value.length - 1 : currentSlide.value - 1;
}

function nextSlide() {
    currentSlide.value = currentSlide.value === gallery.value.length - 1 ? 0 : currentSlide.value + 1;
}

function goToSlide(idx: number) {
    currentSlide.value = idx;
}

const mapsUrl = computed(() => {
    const query = encodeURIComponent(outlet.address ?? outlet.name);
    return `https://www.google.com/maps/dir/?api=1&destination=${query}`;
});
</script>

<template>
    <Head :title="outlet.name" />

    <div class="flex flex-col gap-4">
        <!-- Back + header -->
        <div>
            <Link
                :href="route('member.outlets.index')"
                class="mb-3 inline-flex items-center gap-1 text-sm font-semibold leading-[1.4] text-[#62625b] transition-colors hover:text-[#000000]"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </Link>
            <h1 class="text-xl font-bold leading-[1.2] text-[#000000]">{{ outlet.name }}</h1>
            <p class="mt-0.5 text-sm leading-[1.4] text-[#62625b]">{{ outlet.address ?? '-' }}</p>
        </div>

        <!-- Gallery Carousel -->
        <div v-if="gallery.length" class="relative overflow-hidden rounded-2xl bg-[#f6f6f3]">
            <div class="aspect-[4/3] relative">
                <img
                    :src="gallery[currentSlide]"
                    :alt="`${outlet.name} foto ${currentSlide + 1}`"
                    class="h-full w-full object-cover transition-opacity duration-300"
                />

                <!-- Prev / Next -->
                <button
                    v-if="gallery.length > 1"
                    @click="prevSlide"
                    class="absolute left-2 top-1/2 -translate-y-1/2 flex h-9 w-9 items-center justify-center rounded-full bg-black/50 text-white transition-colors hover:bg-black/70"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button
                    v-if="gallery.length > 1"
                    @click="nextSlide"
                    class="absolute right-2 top-1/2 -translate-y-1/2 flex h-9 w-9 items-center justify-center rounded-full bg-black/50 text-white transition-colors hover:bg-black/70"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- Dots -->
                <div v-if="gallery.length > 1" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
                    <button
                        v-for="(_, idx) in gallery"
                        :key="idx"
                        @click="goToSlide(idx)"
                        :class="[
                            'h-2 rounded-full transition-all',
                            idx === currentSlide ? 'w-6 bg-white' : 'w-2 bg-white/60',
                        ]"
                    />
                </div>
            </div>
        </div>

        <!-- No gallery placeholder -->
        <div v-else class="flex aspect-[4/3] items-center justify-center rounded-2xl bg-[#f6f6f3]">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-[#c8c8c1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="mt-2 text-sm text-[#91918c]">Belum ada foto</p>
            </div>
        </div>

        <!-- Info -->
        <div class="flex flex-col gap-2 rounded-2xl border border-[#dadad3] bg-white p-4">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 shrink-0 text-[#e60023]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <div>
                    <p class="text-sm leading-[1.4] text-[#62625b]">{{ outlet.address ?? '-' }}</p>
                </div>
            </div>
            <div v-if="outlet.phone" class="flex items-center gap-2">
                <svg class="h-5 w-5 shrink-0 text-[#e60023]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <a :href="`tel:${outlet.phone}`" class="text-sm font-semibold leading-[1.4] text-[#000000]">{{ outlet.phone }}</a>
            </div>
            <div v-if="outlet.kasir" class="flex items-center gap-2">
                <svg class="h-5 w-5 shrink-0 text-[#91918c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <p class="text-sm leading-[1.4] text-[#62625b]">Kasir: {{ outlet.kasir.name }}</p>
            </div>
        </div>

        <!-- Direction Button -->
        <a
            :href="mapsUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center justify-center gap-2 rounded-full bg-[#000000] px-6 py-3 text-sm font-bold leading-[1] text-white transition-opacity hover:opacity-90 active:scale-[0.98]"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
            Buka Google Maps
        </a>
    </div>
</template>
