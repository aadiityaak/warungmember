<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { reactive, ref, computed } from 'vue';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Pesanan' },
        ] as BreadcrumbItem[],
    },
});

const { orders, products } = defineProps<{
    orders: Array<{
        id: number;
        user: { name: string };
        status: string;
        total_amount: number;
        notes: string | null;
        created_at: string;
        items: Array<{
            id: number;
            quantity: number;
            price: number;
            subtotal: number;
            product: { id: number; name: string; image: string | null };
        }>;
    }>;
    products: Array<{ id: number; name: string }>;
}>();

const statusLabels: Record<string, string> = {
    pending: 'Menunggu',
    processing: 'Diproses',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
};

const allStatuses = ['pending', 'processing', 'completed', 'cancelled'];

interface EditItem {
    product_id: number;
    name: string;
    quantity: number;
}

const editItems = reactive<EditItem[]>([]);
const editingOrder = ref<{ id: number } | null>(null);
const showEditModal = ref(false);
const selectedProductId = ref<string>('');

const availableProducts = computed(() => {
    const existingIds = editItems.map((i) => i.product_id);
    return products.filter((p) => !existingIds.includes(p.id));
});

const editForm = useForm({
    status: '',
    notes: '',
    items: [] as Array<{ product_id: number; quantity: number }>,
});

function openEdit(order: (typeof orders)[number]) {
    editingOrder.value = order;
    editForm.clearErrors();
    editForm.status = order.status;
    editForm.notes = order.notes ?? '';
    selectedProductId.value = '';
    editItems.splice(0, editItems.length);
    for (const item of order.items) {
        editItems.push({
            product_id: item.product.id,
            name: item.product.name,
            quantity: item.quantity,
        });
    }
    showEditModal.value = true;
}

function addToEditItems() {
    if (!selectedProductId.value) return;
    const product = products.find((p) => p.id === Number(selectedProductId.value));
    if (!product) return;
    editItems.push({
        product_id: product.id,
        name: product.name,
        quantity: 1,
    });
    selectedProductId.value = '';
}

function removeEditItem(productId: number) {
    const idx = editItems.findIndex((i) => i.product_id === productId);
    if (idx !== -1) editItems.splice(idx, 1);
}

function submitEdit() {
    if (!editingOrder.value) return;
    editForm.items = editItems.map((i) => ({
        product_id: i.product_id,
        quantity: i.quantity,
    }));
    editForm.put(route('admin.orders.update', editingOrder.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editingOrder.value = null;
        },
    });
}

function confirmDelete(orderId: number) {
    if (confirm('Hapus pesanan ini?')) {
        router.delete(route('admin.orders.destroy', orderId), {
            preserveScroll: true,
            preserveState: true,
        });
    }
}

function statusClasses(status: string, currentStatus: string): string {
    const isActive = status === currentStatus;
    const base = 'inline-flex cursor-pointer items-center border px-2 py-0.5 text-xs font-semibold transition-colors';

    if (isActive) {
        const activeColors: Record<string, string> = {
            pending: 'bg-yellow-50 text-yellow-700 border-yellow-300 z-10',
            processing: 'bg-blue-50 text-blue-700 border-blue-300 z-10',
            completed: 'bg-green-50 text-green-700 border-green-300 z-10',
            cancelled: 'bg-red-50 text-red-500 border-red-200 z-10',
        };
        return `${base} ${activeColors[status] ?? 'bg-[#e5e5e0] text-[#91918c] border-[#dadad3] z-10'}`;
    }

    return `${base} bg-white text-[#91918c] border-[#dadad3] hover:text-[#000000]`;
}

function statusSegmentClass(index: number, total: number): string {
    if (index === 0) return 'rounded-l-full -mr-px';
    if (index === total - 1) return 'rounded-r-full -ml-px';
    return '-mx-px';
}

function updateStatus(orderId: number, status: string) {
    router.put(route('admin.orders.update', orderId), { status }, {
        preserveScroll: true,
        preserveState: true,
    });
}

function productNames(items: Array<{ product: { name: string }; quantity: number }>): string {
    return items.map((i) => `${i.product.name} x${i.quantity}`).join(', ');
}
</script>

<template>
    <Head title="Manajemen Pesanan" />

    <div class="mx-6 pt-6">
        <!-- Header -->
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Manajemen Pesanan
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Kelola pesanan member
            </p>
        </header>

        <!-- Empty -->
        <div v-if="orders.length === 0" class="rounded-2xl bg-[#f6f6f3] py-16 text-center">
            <p class="text-sm leading-[1.4] text-[#62625b]">Belum ada pesanan.</p>
        </div>

        <!-- Table -->
        <div v-else class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#dadad3]">
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] w-12">#</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Member</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden md:table-cell">Produk</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-right text-sm font-bold leading-[1.4] text-[#000000]">Total</th>
                        <th class="px-4 py-3 text-center text-sm font-bold leading-[1.4] text-[#000000] w-32">Status</th>
                        <th class="px-4 py-3 text-center text-sm font-bold leading-[1.4] text-[#000000] w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="order in orders"
                        :key="order.id"
                        class="border-b border-[#e5e5e0] last:border-0 transition-colors hover:bg-[#fbfbf9]"
                    >
                        <!-- # -->
                        <td class="px-4 py-3">
                            <span class="text-sm leading-[1.4] text-[#91918c]">{{ order.id }}</span>
                        </td>
                        <!-- Member -->
                        <td class="px-4 py-3">
                            <p class="text-sm leading-[1.4] font-semibold text-[#000000]">{{ order.user.name }}</p>
                            <p class="text-xs leading-[1.4] text-[#91918c] md:hidden mt-0.5 truncate max-w-[180px]">{{ productNames(order.items) }}</p>
                        </td>
                        <!-- Produk -->
                        <td class="px-4 py-3 hidden md:table-cell">
                            <p class="text-sm leading-[1.4] text-[#62625b] max-w-xs truncate">{{ productNames(order.items) }}</p>
                            <p v-if="order.notes" class="text-xs leading-[1.4] text-[#91918c] mt-0.5">Catatan: {{ order.notes }}</p>
                        </td>
                        <!-- Tanggal -->
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="text-sm leading-[1.4] text-[#62625b]">
                                {{ new Date(order.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                            </span>
                        </td>
                        <!-- Total -->
                        <td class="px-4 py-3 text-right">
                            <span class="text-sm leading-[1.4] font-semibold text-[#E22625]">Rp{{ order.total_amount.toLocaleString('id-ID') }}</span>
                        </td>
                        <!-- Status -->
                        <td class="px-2 py-3">
                            <div class="inline-flex items-center">
                                <button
                                    v-for="(st, i) in allStatuses"
                                    :key="st"
                                    @click="updateStatus(order.id, st)"
                                    :class="[statusClasses(st, order.status), statusSegmentClass(i, allStatuses.length)]"
                                >
                                    {{ statusLabels[st] }}
                                </button>
                            </div>
                        </td>
                        <!-- Aksi -->
                        <td class="px-2 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <button
                                    @click="openEdit(order)"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#62625b] hover:bg-[#f6f6f3] hover:text-[#000000] transition-colors"
                                    title="Edit"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    @click="confirmDelete(order.id)"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#91918c] hover:bg-red-50 hover:text-red-500 transition-colors"
                                    title="Hapus"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Edit Modal -->
        <Teleport to="body">
            <div
                v-if="showEditModal"
                class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto py-8"
            >
                <!-- Backdrop -->
                <div
                    @click="showEditModal = false"
                    class="fixed inset-0 bg-black/40"
                />
                <!-- Modal -->
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl mx-4">
                    <h3 class="text-lg font-bold text-[#000000] mb-4">Edit Pesanan #{{ editingOrder?.id }}</h3>

                    <div class="space-y-4">
                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-semibold text-[#000000] mb-1.5">Status</label>
                            <select
                                v-model="editForm.status"
                                class="w-full rounded-xl border border-[#dadad3] bg-[#f6f6f3] px-3 py-2.5 text-sm leading-[1.4] text-[#000000] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#E22625]"
                            >
                                <option v-for="st in allStatuses" :key="st" :value="st">
                                    {{ statusLabels[st] }}
                                </option>
                            </select>
                            <p v-if="editForm.errors.status" class="text-xs text-red-500 mt-1">{{ editForm.errors.status }}</p>
                        </div>

                        <!-- Items -->
                        <div>
                            <label class="block text-sm font-semibold text-[#000000] mb-2">Item Pesanan</label>
                            <div v-if="editItems.length === 0" class="text-xs text-[#91918c] py-2">
                                Tidak ada item. Simpan untuk menghapus semua item.
                            </div>
                            <div v-else class="space-y-2">
                                <div
                                    v-for="item in editItems"
                                    :key="item.product_id"
                                    class="flex items-center gap-2 rounded-xl bg-[#f6f6f3] p-2"
                                >
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-[#000000] truncate">{{ item.name }}</p>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <button
                                            @click="item.quantity > 1 ? item.quantity-- : removeEditItem(item.product_id)"
                                            class="flex h-6 w-6 items-center justify-center rounded-full border border-[#dadad3] text-xs font-bold text-[#000000] hover:bg-white"
                                        >-</button>
                                        <span class="w-5 text-center text-xs font-semibold">{{ item.quantity }}</span>
                                        <button
                                            @click="item.quantity++"
                                            class="flex h-6 w-6 items-center justify-center rounded-full border border-[#dadad3] text-xs font-bold text-[#000000] hover:bg-white"
                                        >+</button>
                                    </div>
                                    <button
                                        @click="removeEditItem(item.product_id)"
                                        class="p-1 text-[#91918c] hover:text-red-500"
                                        title="Hapus item"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p v-if="editForm.errors.items" class="text-xs text-red-500 mt-1">{{ editForm.errors.items }}</p>

                            <!-- Tambah Produk -->
                            <div v-if="availableProducts.length" class="mt-2 flex gap-2">
                                <select
                                    v-model="selectedProductId"
                                    class="flex-1 rounded-xl border border-[#dadad3] bg-[#f6f6f3] px-3 py-2 text-sm leading-[1.4] text-[#000000] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#E22625]"
                                >
                                    <option value="" disabled>+ Tambah produk...</option>
                                    <option v-for="p in availableProducts" :key="p.id" :value="p.id">
                                        {{ p.name }}
                                    </option>
                                </select>
                                <button
                                    @click="addToEditItems"
                                    :disabled="!selectedProductId"
                                    class="inline-flex h-9 items-center rounded-full px-4 text-sm font-bold text-white transition-colors"
                                    :class="selectedProductId ? 'bg-[#E22625] hover:opacity-90' : 'bg-[#dadad3] cursor-not-allowed'"
                                >
                                    Tambah
                                </button>
                            </div>
                            <p v-else-if="editItems.length" class="mt-2 text-xs text-[#91918c]">Semua produk sudah ditambahkan.</p>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-semibold text-[#000000] mb-1.5">Catatan</label>
                            <textarea
                                v-model="editForm.notes"
                                rows="2"
                                placeholder="Catatan pesanan..."
                                class="w-full rounded-xl border border-[#dadad3] bg-[#f6f6f3] px-3 py-2 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#E22625]"
                            />
                            <p v-if="editForm.errors.notes" class="text-xs text-red-500 mt-1">{{ editForm.errors.notes }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            @click="showEditModal = false"
                            class="inline-flex h-9 items-center rounded-full px-5 text-sm font-semibold text-[#62625b] hover:bg-[#f6f6f3] transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            @click="submitEdit"
                            :disabled="editForm.processing"
                            class="inline-flex h-9 items-center rounded-full bg-[#E22625] px-5 text-sm font-bold text-white hover:opacity-90 transition-opacity disabled:opacity-50"
                        >
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
