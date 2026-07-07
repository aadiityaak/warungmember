<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';
import SimpleEditor from '@/components/SimpleEditor.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Produk', href: route('admin.products.index') },
            { title: 'Edit' },
        ] as BreadcrumbItem[],
    },
});

const { product, categories } = defineProps<{
    product: {
        id: number;
        name: string;
        description: string | null;
        image: string | null;
        price: number;
        discount_price: number | null;
        discount_end_at: string | null;
        points_earned: number;
        is_active: boolean;
        categories: Array<{ id: number; name: string }>;
    };
    categories: Array<{ id: number; name: string }>;
}>();

const imagePreview = ref<string | null>(product.image ?? null);
const selectedCategories = ref<number[]>(product.categories.map((c) => c.id));

const form = useForm({
    name: product.name,
    description: product.description ?? '',
    image: null as File | null,
    price: product.price,
    discount_price: product.discount_price,
    discount_end_at: product.discount_end_at
        ? new Date(product.discount_end_at).toISOString().slice(0, 16)
        : '',
    points_earned: product.points_earned,
    is_active: product.is_active,
    categories: selectedCategories.value,
});

const hasDiscount = computed({
    get: () => form.discount_price !== null,
    set: (v: boolean) => {
        form.discount_price = v ? 0 : null;
        form.discount_end_at = v ? '' : '';
    },
});

function onImageChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
}

function removeImage() {
    form.image = null;
    imagePreview.value = null;
}

function toggleCategory(id: number) {
    const idx = form.categories.indexOf(id);
    if (idx >= 0) {
        form.categories.splice(idx, 1);
    } else {
        form.categories.push(id);
    }
}

function submit() {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('admin.products.update', product.id));
}
</script>

<template>
    <Head :title="`Edit ${product.name}`" />

    <div class="mx-6 pt-6">
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Edit Produk
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                {{ product.name }}
            </p>
        </header>

        <div class="max-w-2xl rounded-2xl border border-[#dadad3] bg-white p-8">
            <h3 class="mb-6 text-[18px] font-semibold leading-[1.3] text-[#000000]">Form Edit</h3>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Nama -->
                <div>
                    <Label class="mb-1.5 block text-sm font-semibold leading-[1.4] text-[#000000]">Nama Produk</Label>
                    <Input id="name" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Deskripsi (Editor) -->
                <div>
                    <Label class="mb-1.5 block text-sm font-semibold leading-[1.4] text-[#000000]">Deskripsi</Label>
                    <SimpleEditor v-model="form.description" />
                    <InputError :message="form.errors.description" />
                </div>

                <!-- Gambar -->
                <div>
                    <Label class="mb-1.5 block text-sm font-semibold leading-[1.4] text-[#000000]">Gambar Produk</Label>
                    <div class="space-y-3">
                        <div v-if="imagePreview" class="relative w-40 overflow-hidden rounded-2xl border border-[#dadad3]">
                            <img :src="imagePreview" alt="Preview" class="w-full object-cover" />
                            <button type="button" @click="removeImage" class="absolute right-1.5 top-1.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#000000]/60 text-white hover:bg-[#E22625]">
                                &times;
                            </button>
                        </div>
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-[#f6f6f3] px-4 py-2 text-sm font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#e5e5e0]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            {{ imagePreview ? 'Ganti Gambar' : 'Pilih Gambar' }}
                            <input type="file" accept="image/*" class="hidden" @change="onImageChange" />
                        </label>
                    </div>
                    <InputError :message="form.errors.image" />
                </div>

                <!-- Harga -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label class="mb-1.5 block text-sm font-semibold leading-[1.4] text-[#000000]">Harga Normal (Rp)</Label>
                        <Input id="price" v-model="form.price" type="number" min="0" />
                        <InputError :message="form.errors.price" />
                    </div>
                    <div>
                        <Label class="mb-1.5 block text-sm font-semibold leading-[1.4] text-[#000000]">Poin Didapat</Label>
                        <Input id="points" v-model="form.points_earned" type="number" min="0" />
                        <InputError :message="form.errors.points_earned" />
                    </div>
                </div>

                <!-- Diskon -->
                <div class="rounded-2xl bg-[#f6f6f3] p-4">
                    <div class="flex items-center gap-2">
                        <Checkbox id="has_discount" v-model:checked="hasDiscount" />
                        <Label for="has_discount" class="text-sm font-semibold leading-[1.4] text-[#000000]">Aktifkan Harga Diskon</Label>
                    </div>
                    <div v-if="hasDiscount" class="mt-3 grid grid-cols-2 gap-4">
                        <div>
                            <Label class="mb-1.5 block text-sm font-semibold leading-[1.4] text-[#000000]">Harga Diskon (Rp)</Label>
                            <Input id="discount_price" v-model="form.discount_price" type="number" min="0" />
                            <InputError :message="form.errors.discount_price" />
                        </div>
                        <div>
                            <Label class="mb-1.5 block text-sm font-semibold leading-[1.4] text-[#000000]">Berlaku Sampai</Label>
                            <input
                                id="discount_end"
                                v-model="form.discount_end_at"
                                type="datetime-local"
                                class="w-full rounded-2xl border border-[#91918c] bg-white px-4 py-2.5 text-sm leading-[1.4] text-[#000000] focus:border-black focus:outline-none focus:ring-[3px] focus:ring-[#435ee5]/30"
                            />
                            <InputError :message="form.errors.discount_end_at" />
                        </div>
                    </div>
                </div>

                <!-- Kategori -->
                <div>
                    <Label class="mb-2 block text-sm font-semibold leading-[1.4] text-[#000000]">Kategori Produk</Label>
                    <div v-if="categories.length === 0" class="text-sm text-[#91918c]">Belum ada kategori.</div>
                    <div v-else class="flex flex-wrap gap-2">
                        <button
                            v-for="cat in categories"
                            :key="cat.id"
                            type="button"
                            @click="toggleCategory(cat.id)"
                            :class="[
                                'rounded-full px-4 py-1.5 text-sm font-semibold leading-[1.4] transition-colors',
                                form.categories.includes(cat.id)
                                    ? 'bg-[#E22625] text-white'
                                    : 'bg-[#f6f6f3] text-[#000000] hover:bg-[#e5e5e0]',
                            ]"
                        >
                            {{ cat.name }}
                        </button>
                    </div>
                    <InputError :message="form.errors.categories" />
                </div>

                <!-- Aktif -->
                <div class="flex items-center gap-2">
                    <Checkbox id="active" v-model:checked="form.is_active" />
                    <Label for="active" class="text-sm font-semibold leading-[1.4] text-[#000000]">Produk Aktif</Label>
                </div>

                <Button type="submit" :disabled="form.processing" class="w-full">Simpan Perubahan</Button>
            </form>
        </div>
    </div>
</template>
