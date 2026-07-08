<script setup lang="ts">
import { computed } from "vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { Button } from "@/components/ui/button";
import { dashboard } from "@/routes";
import type { BreadcrumbItem } from "@/types";

defineOptions({
    layout: {
        breadcrumbs: [
            { title: "Dashboard", href: dashboard() },
            { title: "Produk" },
        ] as BreadcrumbItem[],
    },
});

const { products } = defineProps<{
    products: {
        data: Array<{
            id: number;
            name: string;
            description: string | null;
            image: string | null;
            price: number;
            discount_price: number | null;
            discount_end_at: string | null;
            points_earned: number;
            is_active: boolean;
            current_price: number;
            is_on_discount: boolean;
            categories: Array<{ id: number; name: string }>;
        }>;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
    };
}>();

const deleteForm = useForm({});

const page = usePage();
const isAdmin = (page.props.auth?.user as Record<string, unknown>)?.role === "admin";

function destroy(id: number) {
    if (confirm("Hapus produk ini?")) {
        deleteForm.delete(route('admin.products.destroy', id));
    }
}

const paginationPages = computed(() => {
    const current = products.current_page;
    const last = products.last_page;
    const pages: (number | "...")[] = [];

    if (last <= 9) {
        for (let i = 1; i <= last; i++) pages.push(i);
        return pages;
    }

    for (let i = 1; i <= 3; i++) pages.push(i);

    if (current > 4) pages.push("...");

    const start = Math.max(4, current - 1);
    const end = Math.min(last - 3, current + 1);

    for (let i = start; i <= end; i++) pages.push(i);

    if (current < last - 3) pages.push("...");

    for (let i = last - 2; i <= last; i++) pages.push(i);

    return pages;
});
</script>

<template>
    <Head title="Kelola Produk" />

    <div class="mx-6 pt-6">
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Kelola Produk
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Daftar menu & produk Warung Mas Mbull
            </p>
        </header>

        <div class="mb-6">
            <Button v-if="isAdmin" as="child">
                <Link :href="route('admin.products.create')">+ Tambah Produk</Link>
            </Button>
        </div>

        <div v-if="products.data.length === 0" class="rounded-2xl bg-[#f6f6f3] py-16 text-center">
            <p class="text-sm leading-[1.4] text-[#62625b]">Belum ada produk ditambahkan.</p>
        </div>

        <div v-else class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#dadad3]">
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] w-16">Gambar</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Nama</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden sm:table-cell">Kategori</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Harga</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden md:table-cell">Poin</th>
                        <th class="px-4 py-3 text-center text-sm font-bold leading-[1.4] text-[#000000] w-20">Status</th>
                        <th v-if="isAdmin" class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="product in products.data"
                        :key="product.id"
                        class="border-b border-[#e5e5e0] last:border-0 transition-colors hover:bg-[#fbfbf9]"
                    >
                        <td class="px-4 py-3">
                            <img
                                v-if="product.image"
                                :src="product.image"
                                :alt="product.name"
                                class="h-10 w-10 rounded-lg object-cover border border-[#dadad3]"
                            />
                            <div
                                v-else
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#f6f6f3]"
                            >
                                <svg class="h-4 w-4 text-[#91918c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm leading-[1.4] font-semibold text-[#000000]">{{ product.name }}</p>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <div v-if="product.categories.length" class="flex flex-wrap gap-1">
                                <span
                                    v-for="cat in product.categories"
                                    :key="cat.id"
                                    class="rounded-full bg-[#f6f6f3] px-2 py-0.5 text-[11px] font-semibold leading-[1.4] text-[#62625b]"
                                >
                                    {{ cat.name }}
                                </span>
                            </div>
                            <span v-else class="text-xs text-[#91918c]">-</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-baseline gap-1.5">
                                <span v-if="product.is_on_discount" class="text-sm font-semibold leading-[1.4] text-[#E22625]">
                                    Rp{{ product.current_price.toLocaleString('id-ID') }}
                                </span>
                                <span
                                    :class="[
                                        'text-sm leading-[1.4]',
                                        product.is_on_discount
                                            ? 'text-[#91918c] line-through text-xs'
                                            : 'font-semibold text-[#000000]',
                                    ]"
                                >
                                    Rp{{ product.price.toLocaleString('id-ID') }}
                                </span>
                            </div>
                            <p v-if="product.is_on_discount && product.discount_end_at" class="mt-0.5 text-[11px] leading-[1.4] text-[#91918c]">
                                s/d {{ new Date(product.discount_end_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) }}
                            </p>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            <span class="text-sm font-semibold leading-[1.4] text-[#E22625]">{{ product.points_earned }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span
                                :class="[
                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold leading-[1.4]',
                                    product.is_active
                                        ? 'bg-[#E22625]/10 text-[#E22625]'
                                        : 'bg-[#e5e5e0] text-[#91918c]',
                                ]"
                            >
                                {{ product.is_active ? "Aktif" : "Nonaktif" }}
                            </span>
                        </td>
                        <td v-if="isAdmin" class="px-2 py-3">
                            <div class="flex gap-1">
                                <Link
                                    :href="route('admin.products.edit', product.id)"
                                    class="inline-flex h-8 items-center rounded-full bg-[#f6f6f3] px-3 text-xs font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#e5e5e0]"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="destroy(product.id)"
                                    :disabled="deleteForm.processing"
                                    class="inline-flex h-8 items-center rounded-full bg-[#f6f6f3] px-3 text-xs font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#E22625] hover:text-white"
                                >
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="products.last_page > 1" class="flex items-center justify-between border-t border-[#dadad3] px-5 py-3">
                <span class="text-sm leading-[1.4] text-[#62625b]">
                    {{ products.from }}-{{ products.to }} dari {{ products.total }}
                </span>
                <div class="flex gap-1">
                    <template v-for="(page, idx) in paginationPages" :key="idx">
                        <span
                            v-if="page === '...'"
                            class="inline-flex h-9 w-9 items-center justify-center text-sm font-bold leading-[1] text-[#62625b]"
                        >
                            ...
                        </span>
                        <Link
                            v-else
                            :href="route('admin.products.index', { page })"
                            :class="[
                                'inline-flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold leading-[1] transition-colors',
                                page === products.current_page
                                    ? 'bg-[#000000] text-white'
                                    : 'text-[#000000] hover:bg-[#f6f6f3]',
                            ]"
                        >
                            {{ page }}
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
