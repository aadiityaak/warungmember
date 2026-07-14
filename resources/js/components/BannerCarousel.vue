// BannerCarousel.vue — reusable image carousel with auto-slide
<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps<{
    images: string[];
    altPrefix?: string;
}>();

const current = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

function next() {
    current.value = (current.value + 1) % props.images.length;
}

function prev() {
    current.value = (current.value - 1 + props.images.length) % props.images.length;
}

function goTo(i: number) {
    current.value = i;
    resetTimer();
}

function resetTimer() {
    if (timer) clearInterval(timer);
    timer = setInterval(next, 4000);
}

onMounted(() => resetTimer());
onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <div class="relative overflow-hidden rounded-2xl">
        <button
            @click="prev"
            class="absolute left-2 top-1/2 z-10 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-white/80 text-[#000000] shadow transition-colors hover:bg-white"
            aria-label="Sebelumnya"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <button
            @click="next"
            class="absolute right-2 top-1/2 z-10 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-white/80 text-[#000000] shadow transition-colors hover:bg-white"
            aria-label="Selanjutnya"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <div
            class="flex transition-transform duration-500 ease-out"
            :style="{ transform: `translateX(-${current * 100}%)` }"
        >
            <img
                v-for="(src, i) in images"
                :key="i"
                :src="src"
                :alt="`${altPrefix ?? 'Banner'} ${i + 1}`"
                class="w-full shrink-0 object-cover"
            />
        </div>

        <!-- Dots -->
        <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-1.5">
            <button
                v-for="(_, i) in images"
                :key="i"
                @click="goTo(i)"
                class="h-2 rounded-full transition-all"
                :class="
                    i === current
                        ? 'w-5 bg-white'
                        : 'w-2 bg-white/50 hover:bg-white/80'
                "
                :aria-label="`Ke banner ${i + 1}`"
            />
        </div>
    </div>
</template>
