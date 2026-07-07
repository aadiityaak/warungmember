<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useCart } from '@/composables/useCart';

defineOptions({ layout: null }); // use default MemberLayout

const { products, categories } = defineProps<{
    products: Array<Record<string, any>>;
    categories: Array<Record<string, any>>;
}>();

const cart = useCart();
const selectedCategory = ref<string>('');
const searchQuery = ref('');

const filteredProducts = computed(() => {
    let list = products;
    if (selectedCategory.value) {
        list = list.filter((p) =>
            p.categories?.some((c) => c.name === selectedCategory.value)
        );
    }
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter((p) => p.name.toLowerCase().includes(q));
    }
    return list;
});

function isInCart(productId: number): boolean {
    return cart.items.some((i) => i.product_id === productId);
}

function addToCart(product: Product) {
    cart.add({
        id: product.id,
        name: product.name,
        price: product.price,
        current_price: product.current_price,
        image: product.image,
    });
}

const statusLabels: Record<string, string> = {
    pending: 'Menunggu',
    processing: 'Diproses',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
};
</script>

<template>
    <Head title="Menu Produk" />

    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div>
            <h1 class="text-xl font-bold leading-[1.2] text-[#000000]">Menu Produk</h1>
            <p class="mt-0.5 text-sm leading-[1.4] text-[#62625b]">Pilih menu favorit kamu</p>
        </div>

        <!-- Search -->
        <input
            v-model="searchQuery"
            placeholder="Cari menu..."
            class="w-full rounded-full border border-[#dadad3] bg-white px-4 py-2.5 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:outline-none focus:ring-2 focus:ring-[#E22625]"
        />

        <!-- Category Chips -->
        <div class="flex gap-2 overflow-x-auto pb-1">
            <button
                @click="selectedCategory = ''"
                :class="[
                    'shrink-0 rounded-full px-4 py-1.5 text-xs font-semibold leading-[1.4] transition-colors border',
                    selectedCategory === ''
                        ? 'bg-[#E22625] text-white border-[#E22625]'
                        : 'bg-white text-[#000000] border-[#dadad3] hover:border-[#E22625]',
                ]"
            >
                Semua
            </button>
            <button
                v-for="cat in categories"
                :key="cat.id"
                @click="selectedCategory = cat.name"
                :class="[
                    'shrink-0 rounded-full px-4 py-1.5 text-xs font-semibold leading-[1.4] transition-colors border',
                    selectedCategory === cat.name
                        ? 'bg-[#E22625] text-white border-[#E22625]'
                        : 'bg-white text-[#000000] border-[#dadad3] hover:border-[#E22625]',
                ]"
            >
                {{ cat.name }}
            </button>
        </div>

        <!-- Product Grid -->
        <div v-if="filteredProducts.length === 0" class="py-12 text-center">
            <p class="text-sm text-[#91918c]">Produk tidak ditemukan.</p>
        </div>

        <div v-else class="grid grid-cols-2 gap-3">
            <div
                v-for="product in filteredProducts"
                :key="product.id"
                class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white transition-shadow hover:shadow-md"
            >
                <!-- Image -->
                <div class="relative aspect-square overflow-hidden bg-[#f6f6f3]">
                    <img
                        v-if="product.image"
                        :src="product.image"
                        :alt="product.name"
                        class="h-full w-full object-cover"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center">
                        <svg class="h-10 w-10 text-[#dadad3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <!-- Discount badge -->
                    <div v-if="product.is_on_discount" class="absolute left-2 top-2 rounded-full bg-[#E22625] px-2 py-0.5 text-[10px] font-bold text-white">
                        DISKON
                    </div>
                </div>

                <!-- Info -->
                <div class="p-3">
                    <h3 class="text-sm font-semibold leading-[1.3] text-[#000000] line-clamp-2">{{ product.name }}</h3>
                    <p class="mt-0.5 text-xs leading-[1.4] text-[#91918c]">+{{ product.points_earned }} poin</p>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span class="text-sm font-bold text-[#E22625]">Rp{{ product.current_price.toLocaleString('id-ID') }}</span>
                        <span v-if="product.is_on_discount" class="text-xs text-[#91918c] line-through">Rp{{ product.price.toLocaleString('id-ID') }}</span>
                    </div>

                    <!-- Add to Cart -->
                    <button
                        @click="addToCart(product)"
                        class="mt-2 flex w-full items-center justify-center gap-1 rounded-full py-1.5 text-xs font-bold transition-colors"
                        :class="
                            isInCart(product.id)
                                ? 'bg-[#E22625]/10 text-[#E22625]'
                                : 'bg-[#f6f6f3] text-[#000000] hover:bg-[#E22625] hover:text-white'
                        "
                    >
                        <svg v-if="isInCart(product.id)" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        {{ isInCart(product.id) ? 'Di Keranjang' : '+ Keranjang' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
