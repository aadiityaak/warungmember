<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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

const { orders, products, members, outlets } = defineProps<{
    orders: {
        data: Array<{
            id: number;
            user: { name: string };
            status: string;
            total_amount: number;
            paid_amount: number | null;
            change: number | null;
            payment_method: string;
            notes: string | null;
            created_at: string;
            outlet: { id: number; name: string } | null;
            items: Array<{
                id: number;
                quantity: number;
                price: number;
                subtotal: number;
                product: { id: number; name: string; image: string | null };
            }>;
        }>;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
    };
    products: Array<{
        id: number;
        name: string;
        image: string | null;
        price: number;
        discount_price: number | null;
        discount_end_at: string | null;
    }>;
    members: Array<{
        id: number;
        name: string;
        avatar: string | null;
        member: { member_code: string } | null;
    }>;
    outlets: Array<{ id: number; name: string }>;
}>();

function formatRupiah(n: number): string {
    return 'Rp ' + n.toLocaleString('id-ID');
}

function currentPrice(p: {
    price: number;
    discount_price: number | null;
    discount_end_at: string | null;
}): number {
    if (!p.discount_price) return p.price;
    if (p.discount_end_at && new Date(p.discount_end_at) < new Date())
        return p.price;
    return p.discount_price;
}

function isOnDiscount(p: {
    price: number;
    discount_price: number | null;
    discount_end_at: string | null;
}): boolean {
    return currentPrice(p) < p.price;
}

const statusLabels: Record<string, string> = {
    pending: 'Menunggu',
    processing: 'Diproses',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
};

const paymentLabels: Record<string, string> = {
    cash: 'Tunai',
    qris: 'QRIS',
    transfer: 'Transfer',
};

const allStatuses = ['pending', 'processing', 'completed', 'cancelled'];
const paymentMethods = ['cash', 'qris', 'transfer'];

interface EditItem {
    product_id: number;
    name: string;
    quantity: number;
}

// --- Edit Modal State ---
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

// --- Create Modal State ---
const showCreateModal = ref(false);
const createItems = reactive<EditItem[]>([]);
const createSelectedProduct = ref<string>('');

const createAvailableProducts = computed(() => {
    const existingIds = createItems.map((i) => i.product_id);
    return products.filter((p) => !existingIds.includes(p.id));
});

const createForm = useForm({
    user_id: '',
    outlet_id: '',
    payment_method: 'cash',
    paid_amount: '',
    notes: '',
    items: [] as Array<{ product_id: number; quantity: number }>,
});

const totalCreateAmount = computed(() => {
    return createItems.reduce((sum, item) => {
        const product = products.find((p) => p.id === item.product_id);
        if (!product) return sum;
        return sum + currentPrice(product) * item.quantity;
    }, 0);
});

// --- Member Search ---
const memberSearch = ref('');
const memberDropdownOpen = ref(false);
const memberHighlightIndex = ref(0);
const memberInputRef = ref<HTMLInputElement | null>(null);

const filteredMembers = computed(() => {
    const q = memberSearch.value.toLowerCase();
    if (!q) return members;
    return members.filter((m) => {
        const nameMatch = m.name.toLowerCase().includes(q);
        const codeMatch = m.member?.member_code?.toLowerCase().includes(q);
        const idMatch = String(m.id) === q;
        return nameMatch || codeMatch || idMatch;
    });
});

function selectMember(m: { id: number; name: string }) {
    createForm.user_id = String(m.id);
    memberSearch.value = m.name;
    memberDropdownOpen.value = false;
}

function onMemberInputFocus() {
    memberDropdownOpen.value = true;
    memberHighlightIndex.value = 0;
}

function onMemberInputBlur() {
    setTimeout(() => {
        memberDropdownOpen.value = false;
    }, 200);
}

function onMemberKeydown(e: KeyboardEvent) {
    if (!memberDropdownOpen.value) return;
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        memberHighlightIndex.value = Math.min(
            memberHighlightIndex.value + 1,
            filteredMembers.value.length - 1,
        );
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        memberHighlightIndex.value = Math.max(
            memberHighlightIndex.value - 1,
            0,
        );
    } else if (
        e.key === 'Enter' &&
        filteredMembers.value[memberHighlightIndex.value]
    ) {
        e.preventDefault();
        selectMember(filteredMembers.value[memberHighlightIndex.value]);
    } else if (e.key === 'Escape') {
        memberDropdownOpen.value = false;
    }
}

function openCreate() {
    createForm.clearErrors();
    createForm.user_id = '';
    createForm.outlet_id = '';
    createForm.payment_method = 'cash';
    createForm.notes = '';
    createSelectedProduct.value = '';
    createItems.splice(0, createItems.length);
    memberSearch.value = '';
    memberDropdownOpen.value = false;
    showCreateModal.value = true;
}

function addToCreateItems() {
    if (!createSelectedProduct.value) return;
    const product = products.find(
        (p) => p.id === Number(createSelectedProduct.value),
    );
    if (!product) return;
    createItems.push({
        product_id: product.id,
        name: product.name,
        quantity: 1,
    });
    createSelectedProduct.value = '';
}

function addToCreateItemsCard(product: { id: number; name: string }) {
    if (createItems.some((i) => i.product_id === product.id)) return;
    createItems.push({
        product_id: product.id,
        name: product.name,
        quantity: 1,
    });
}

function removeCreateItem(productId: number) {
    const idx = createItems.findIndex((i) => i.product_id === productId);
    if (idx !== -1) createItems.splice(idx, 1);
}

function submitCreate() {
    createForm.items = createItems.map((i) => ({
        product_id: i.product_id,
        quantity: i.quantity,
    }));
    createForm.post(route('admin.orders.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
        },
    });
}

function openEdit(order: (typeof orders.data)[number]) {
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
    const product = products.find(
        (p) => p.id === Number(selectedProductId.value),
    );
    if (!product) return;
    editItems.push({
        product_id: product.id,
        name: product.name,
        quantity: 1,
    });
    selectedProductId.value = '';
}

function addToEditItemsCard(product: { id: number; name: string }) {
    if (editItems.some((i) => i.product_id === product.id)) return;
    editItems.push({
        product_id: product.id,
        name: product.name,
        quantity: 1,
    });
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

function statusColor(status: string): string {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-50 text-yellow-700 border-yellow-300',
        processing: 'bg-blue-50 text-blue-700 border-blue-300',
        completed: 'bg-green-50 text-green-700 border-green-300',
        cancelled: 'bg-red-50 text-red-500 border-red-200',
    };
    return colors[status] ?? 'bg-[#e5e5e0] text-[#91918c] border-[#dadad3]';
}

function updateStatus(orderId: number, e: Event) {
    const status = (e.target as HTMLSelectElement).value;
    router.put(
        route('admin.orders.update', orderId),
        { status },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
}

function productNames(
    items: Array<{ product: { name: string }; quantity: number }>,
): string {
    return items.map((i) => `${i.product.name} x${i.quantity}`).join(', ');
}

function printOrder(orderId: number) {
    window.open(route('admin.orders.receipt', orderId), '_blank');
}

const paginationPages = computed(() => {
    const current = orders.current_page;
    const last = orders.last_page;
    const pages: (number | '...')[] = [];

    if (last <= 9) {
        for (let i = 1; i <= last; i++) pages.push(i);
        return pages;
    }

    for (let i = 1; i <= 3; i++) pages.push(i);

    if (current > 4) pages.push('...');

    const start = Math.max(4, current - 1);
    const end = Math.min(last - 3, current + 1);

    for (let i = start; i <= end; i++) pages.push(i);

    if (current < last - 3) pages.push('...');

    for (let i = last - 2; i <= last; i++) pages.push(i);

    return pages;
});
</script>

<template>
    <Head title="Manajemen Pesanan" />

    <div class="mx-6 pt-6">
        <!-- Header -->
        <header class="mb-6 flex items-center justify-between">
            <div class="space-y-0.5">
                <h2
                    class="text-[28px] leading-[1.2] font-bold tracking-[-1.2px] text-[#000000]"
                >
                    Manajemen Pesanan
                </h2>
                <p class="text-sm leading-[1.4] text-[#62625b]">
                    Kelola pesanan member
                </p>
            </div>
            <button
                @click="openCreate"
                class="inline-flex h-9 items-center gap-1.5 rounded-full bg-[#E22625] px-5 text-sm font-bold text-white transition-opacity hover:opacity-90"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v16m8-8H4"
                    />
                </svg>
                Buat Pesanan
            </button>
        </header>

        <!-- Empty -->
        <div
            v-if="orders.data.length === 0"
            class="rounded-2xl bg-[#f6f6f3] py-16 text-center"
        >
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Belum ada pesanan.
            </p>
        </div>

        <!-- Table -->
        <div
            v-else
            class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white"
        >
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-[#dadad3]">
                            <th
                                class="w-12 px-4 py-3 text-left text-sm leading-[1.4] font-bold text-[#000000]"
                            >
                                #
                            </th>
                            <th
                                class="px-4 py-3 text-left text-sm leading-[1.4] font-bold text-[#000000]"
                            >
                                Member
                            </th>
                            <th
                                class="hidden px-4 py-3 text-left text-sm leading-[1.4] font-bold text-[#000000] md:table-cell"
                            >
                                Produk
                            </th>
                            <th
                                class="hidden px-4 py-3 text-left text-sm leading-[1.4] font-bold text-[#000000] sm:table-cell"
                            >
                                Tanggal
                            </th>
                            <th
                                class="px-4 py-3 text-right text-sm leading-[1.4] font-bold text-[#000000]"
                            >
                                Total
                            </th>
                            <th
                                class="w-32 px-4 py-3 text-center text-sm leading-[1.4] font-bold text-[#000000]"
                            >
                                Status
                            </th>
                            <th
                                class="w-20 px-4 py-3 text-center text-sm leading-[1.4] font-bold text-[#000000]"
                            >
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in orders.data"
                            :key="order.id"
                            class="border-b border-[#e5e5e0] transition-colors last:border-0 hover:bg-[#fbfbf9]"
                        >
                            <!-- # -->
                            <td class="px-4 py-3">
                                <span
                                    class="text-sm leading-[1.4] text-[#91918c]"
                                    >{{ order.id }}</span
                                >
                            </td>
                            <!-- Member -->
                            <td class="px-4 py-3">
                                <p
                                    class="text-sm leading-[1.4] font-semibold text-[#000000]"
                                >
                                    {{ order.user.name }}
                                </p>
                                <p
                                    class="mt-0.5 max-w-[180px] truncate text-xs leading-[1.4] text-[#91918c] md:hidden"
                                >
                                    {{ productNames(order.items) }}
                                </p>
                            </td>
                            <!-- Produk -->
                            <td class="hidden px-4 py-3 md:table-cell">
                                <p
                                    class="max-w-xs truncate text-sm leading-[1.4] text-[#62625b]"
                                >
                                    {{ productNames(order.items) }}
                                </p>
                                <p
                                    v-if="order.notes"
                                    class="mt-0.5 text-xs leading-[1.4] text-[#91918c]"
                                >
                                    Catatan: {{ order.notes }}
                                </p>
                            </td>
                            <!-- Tanggal -->
                            <td class="hidden px-4 py-3 sm:table-cell">
                                <span
                                    class="text-sm leading-[1.4] text-[#62625b]"
                                >
                                    {{
                                        new Date(
                                            order.created_at,
                                        ).toLocaleDateString('id-ID', {
                                            day: 'numeric',
                                            month: 'short',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                        })
                                    }}
                                </span>
                            </td>
                            <!-- Total -->
                            <td class="px-4 py-3 text-right">
                                <span
                                    class="text-sm leading-[1.4] font-semibold text-[#E22625]"
                                    >Rp{{
                                        order.total_amount.toLocaleString(
                                            'id-ID',
                                        )
                                    }}</span
                                >
                            </td>
                            <!-- Status -->
                            <td class="px-3 py-3">
                                <select
                                    :value="order.status"
                                    @change="updateStatus(order.id, $event)"
                                    :class="[
                                        'cursor-pointer rounded-lg border px-2 py-1.5 pr-7 text-xs font-semibold',
                                        statusColor(order.status),
                                    ]"
                                >
                                    <option
                                        v-for="st in allStatuses"
                                        :key="st"
                                        :value="st"
                                    >
                                        {{ statusLabels[st] }}
                                    </option>
                                </select>
                            </td>
                            <!-- Aksi -->
                            <td class="px-2 py-3">
                                <div
                                    class="flex items-center justify-center gap-1"
                                >
                                    <button
                                        @click="printOrder(order.id)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#62625b] transition-colors hover:bg-[#f6f6f3] hover:text-[#000000]"
                                        title="Cetak Struk"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        @click="openEdit(order)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#62625b] transition-colors hover:bg-[#f6f6f3] hover:text-[#000000]"
                                        title="Edit"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        @click="confirmDelete(order.id)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#91918c] transition-colors hover:bg-red-50 hover:text-red-500"
                                        title="Hapus"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="orders.last_page > 1"
                class="flex items-center justify-between border-t border-[#dadad3] px-5 py-3"
            >
                <span class="text-sm leading-[1.4] text-[#62625b]">
                    {{ orders.from }}-{{ orders.to }} dari {{ orders.total }}
                </span>
                <div class="flex items-center gap-1">
                    <Link
                        v-if="orders.current_page > 1"
                        :href="
                            route('admin.orders.index', {
                                page: orders.current_page - 1,
                            })
                        "
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full text-sm text-[#62625b] transition-colors hover:bg-[#f6f6f3]"
                        title="Sebelumnya"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                    </Link>
                    <template v-for="page in paginationPages" :key="page">
                        <span
                            v-if="page === '...'"
                            class="inline-flex h-9 w-9 items-center justify-center text-xs text-[#91918c]"
                            >...</span
                        >
                        <Link
                            v-else
                            :href="route('admin.orders.index', { page })"
                            :class="[
                                'inline-flex h-9 w-9 items-center justify-center rounded-full text-sm leading-[1] font-bold transition-colors',
                                page === orders.current_page
                                    ? 'bg-[#000000] text-white'
                                    : 'text-[#000000] hover:bg-[#f6f6f3]',
                            ]"
                        >
                            {{ page }}
                        </Link>
                    </template>
                    <Link
                        v-if="orders.current_page < orders.last_page"
                        :href="
                            route('admin.orders.index', {
                                page: orders.current_page + 1,
                            })
                        "
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full text-sm text-[#62625b] transition-colors hover:bg-[#f6f6f3]"
                        title="Berikutnya"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Teleport to="body">
            <div
                v-if="showCreateModal"
                class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto py-8"
            >
                <div
                    @click="showCreateModal = false"
                    class="fixed inset-0 bg-black/40"
                />
                <div
                    class="relative mx-4 w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl"
                >
                    <h3 class="mb-4 text-lg font-bold text-[#000000]">
                        Buat Pesanan Baru
                    </h3>

                    <div class="space-y-4">
                        <!-- Member Search -->
                        <div class="relative">
                            <label
                                class="mb-1.5 block text-sm font-semibold text-[#000000]"
                                >Member</label
                            >
                            <div class="relative">
                                <svg
                                    class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-[#91918c]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>
                                <input
                                    ref="memberInputRef"
                                    v-model="memberSearch"
                                    type="text"
                                    placeholder="Cari nama atau kode member..."
                                    @focus="onMemberInputFocus"
                                    @blur="onMemberInputBlur"
                                    @keydown="onMemberKeydown"
                                    autocomplete="off"
                                    class="w-full rounded-xl border border-[#dadad3] bg-[#f6f6f3] py-2.5 pr-3 pl-9 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:bg-white focus:ring-2 focus:ring-[#E22625] focus:outline-none"
                                />
                            </div>

                            <!-- Dropdown -->
                            <div
                                v-if="
                                    memberDropdownOpen && filteredMembers.length
                                "
                                class="absolute z-10 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-[#dadad3] bg-white shadow-lg"
                            >
                                <button
                                    v-for="(m, i) in filteredMembers"
                                    :key="m.id"
                                    type="button"
                                    @mousedown.prevent="selectMember(m)"
                                    :class="[
                                        'flex w-full items-center gap-3 px-3 py-2.5 text-left transition-colors',
                                        i === memberHighlightIndex
                                            ? 'bg-[#f6f6f3]'
                                            : 'hover:bg-[#f6f6f3]',
                                    ]"
                                >
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#e5e5e0]"
                                    >
                                        <img
                                            v-if="m.avatar"
                                            :src="m.avatar"
                                            :alt="m.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <svg
                                            v-else
                                            class="h-4 w-4 text-[#91918c]"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="truncate text-sm leading-[1.3] font-semibold text-[#000000]"
                                        >
                                            {{ m.name }}
                                        </p>
                                        <p
                                            class="text-xs leading-[1.4] text-[#91918c]"
                                        >
                                            {{
                                                m.member?.member_code
                                                    ? '# ' +
                                                      m.member.member_code
                                                    : 'ID: ' + m.id
                                            }}
                                        </p>
                                    </div>
                                </button>
                            </div>
                            <div
                                v-if="
                                    memberDropdownOpen &&
                                    !filteredMembers.length &&
                                    memberSearch
                                "
                                class="absolute z-10 mt-1 w-full rounded-xl border border-[#dadad3] bg-white p-3 text-center text-sm text-[#91918c]"
                            >
                                Member tidak ditemukan
                            </div>
                            <p
                                v-if="createForm.errors.user_id"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ createForm.errors.user_id }}
                            </p>
                        </div>

                        <!-- Outlet -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-semibold text-[#000000]"
                                >Outlet</label
                            >
                            <select
                                v-model="createForm.outlet_id"
                                class="w-full rounded-xl border border-[#dadad3] bg-[#f6f6f3] px-3 py-2.5 text-sm leading-[1.4] text-[#000000] focus:bg-white focus:ring-2 focus:ring-[#E22625] focus:outline-none"
                            >
                                <option value="" disabled>
                                    Pilih outlet...
                                </option>
                                <option
                                    v-for="o in outlets"
                                    :key="o.id"
                                    :value="o.id"
                                >
                                    {{ o.name }}
                                </option>
                            </select>
                            <p
                                v-if="createForm.errors.outlet_id"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ createForm.errors.outlet_id }}
                            </p>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-semibold text-[#000000]"
                                >Metode Pembayaran</label
                            >
                            <div class="flex gap-2">
                                <button
                                    v-for="pm in paymentMethods"
                                    :key="pm"
                                    type="button"
                                    @click="createForm.payment_method = pm"
                                    :class="[
                                        'flex-1 rounded-xl border px-3 py-2 text-sm font-semibold transition-colors',
                                        createForm.payment_method === pm
                                            ? 'border-[#E22625] bg-red-50 text-[#E22625]'
                                            : 'border-[#dadad3] bg-[#f6f6f3] text-[#62625b] hover:border-[#91918c]',
                                    ]"
                                >
                                    {{ paymentLabels[pm] }}
                                </button>
                            </div>
                            <p
                                v-if="createForm.errors.payment_method"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ createForm.errors.payment_method }}
                            </p>
                        </div>

                        <!-- Paid Amount (Cash only) -->
                        <div v-if="createForm.payment_method === 'cash'">
                            <label
                                class="mb-1.5 block text-sm font-semibold text-[#000000]"
                                >Jumlah Dibayar (Tunai)</label
                            >
                            <div class="relative">
                                <span
                                    class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-sm font-semibold text-[#91918c]"
                                >Rp</span>
                                <input
                                    v-model="createForm.paid_amount"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    class="w-full rounded-xl border border-[#dadad3] bg-[#f6f6f3] py-2.5 pr-3 pl-9 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:bg-white focus:ring-2 focus:ring-[#E22625] focus:outline-none"
                                />
                            </div>
                            <p
                                v-if="createForm.errors.paid_amount"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ createForm.errors.paid_amount }}
                            </p>
                            <p
                                v-if="createForm.payment_method === 'cash' && createForm.paid_amount && totalCreateAmount > 0"
                                class="mt-1 text-xs"
                                :class="Number(createForm.paid_amount) >= totalCreateAmount ? 'text-green-600' : 'text-red-500'"
                            >
                                Kembalian: Rp{{ (Math.max(0, Number(createForm.paid_amount) - totalCreateAmount)).toLocaleString('id-ID') }}
                            </p>
                        </div>

                        <!-- Items -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-[#000000]"
                                >Item Pesanan</label
                            >
                            <div
                                v-if="createItems.length === 0"
                                class="py-2 text-xs text-[#91918c]"
                            >
                                Tambahkan minimal 1 produk.
                            </div>
                            <div v-else class="space-y-2">
                                <div
                                    v-for="item in createItems"
                                    :key="item.product_id"
                                    class="flex items-center gap-2 rounded-xl bg-[#f6f6f3] p-2"
                                >
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="truncate text-sm font-semibold text-[#000000]"
                                        >
                                            {{ item.name }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <button
                                            @click="
                                                item.quantity > 1
                                                    ? item.quantity--
                                                    : removeCreateItem(
                                                          item.product_id,
                                                      )
                                            "
                                            class="flex h-6 w-6 items-center justify-center rounded-full border border-[#dadad3] text-xs font-bold text-[#000000] hover:bg-white"
                                        >
                                            -
                                        </button>
                                        <span
                                            class="w-5 text-center text-xs font-semibold"
                                            >{{ item.quantity }}</span
                                        >
                                        <button
                                            @click="item.quantity++"
                                            class="flex h-6 w-6 items-center justify-center rounded-full border border-[#dadad3] text-xs font-bold text-[#000000] hover:bg-white"
                                        >
                                            +
                                        </button>
                                    </div>
                                    <button
                                        @click="
                                            removeCreateItem(item.product_id)
                                        "
                                        class="p-1 text-[#91918c] hover:text-red-500"
                                        title="Hapus item"
                                    >
                                        <svg
                                            class="h-3.5 w-3.5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p
                                v-if="createForm.errors.items"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ createForm.errors.items }}
                            </p>

                            <!-- Product Cards -->
                            <div class="mt-2">
                                <p
                                    class="mb-2 text-xs font-semibold text-[#62625b]"
                                >
                                    Pilih Produk
                                </p>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        v-for="p in products"
                                        :key="p.id"
                                        type="button"
                                        @click="addToCreateItemsCard(p)"
                                        :disabled="
                                            createItems.some(
                                                (i) => i.product_id === p.id,
                                            )
                                        "
                                        class="flex flex-col items-center gap-1 rounded-xl border p-2 text-center transition-all disabled:cursor-not-allowed disabled:opacity-40"
                                        :class="
                                            createItems.some(
                                                (i) => i.product_id === p.id,
                                            )
                                                ? 'border-[#E22625] bg-red-50'
                                                : 'border-[#dadad3] bg-[#f6f6f3] hover:border-[#E22625] hover:shadow-sm'
                                        "
                                    >
                                        <div
                                            class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-lg bg-white"
                                        >
                                            <img
                                                v-if="p.image"
                                                :src="p.image"
                                                :alt="p.name"
                                                class="h-full w-full object-cover"
                                            />
                                            <svg
                                                v-else
                                                class="h-5 w-5 text-[#c8c8c1]"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                                />
                                            </svg>
                                        </div>
                                        <span
                                            class="line-clamp-2 text-[10px] leading-[1.3] font-semibold text-[#000000]"
                                            >{{ p.name }}</span
                                        >
                                        <span
                                            class="text-[10px] font-bold text-[#E22625]"
                                            >{{
                                                formatRupiah(currentPrice(p))
                                            }}</span
                                        >
                                        <span
                                            v-if="isOnDiscount(p)"
                                            class="text-[9px] text-[#91918c] line-through"
                                            >{{ formatRupiah(p.price) }}</span
                                        >
                                        <span
                                            v-if="
                                                createItems.some(
                                                    (i) =>
                                                        i.product_id === p.id,
                                                )
                                            "
                                            class="text-[9px] font-bold text-[#E22625]"
                                            >✓ Ditambahkan</span
                                        >
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-semibold text-[#000000]"
                                >Catatan</label
                            >
                            <textarea
                                v-model="createForm.notes"
                                rows="2"
                                placeholder="Catatan pesanan..."
                                class="w-full rounded-xl border border-[#dadad3] bg-[#f6f6f3] px-3 py-2 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:bg-white focus:ring-2 focus:ring-[#E22625] focus:outline-none"
                            />
                            <p
                                v-if="createForm.errors.notes"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ createForm.errors.notes }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            @click="showCreateModal = false"
                            class="inline-flex h-9 items-center rounded-full px-5 text-sm font-semibold text-[#62625b] transition-colors hover:bg-[#f6f6f3]"
                        >
                            Batal
                        </button>
                        <button
                            @click="submitCreate"
                            :disabled="createForm.processing"
                            class="inline-flex h-9 items-center rounded-full bg-[#E22625] px-5 text-sm font-bold text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                        >
                            Buat Pesanan
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Edit Modal -->
        <Teleport to="body">
            <div
                v-if="showEditModal"
                class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto py-8"
            >
                <div
                    @click="showEditModal = false"
                    class="fixed inset-0 bg-black/40"
                />
                <div
                    class="relative mx-4 w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl"
                >
                    <h3 class="mb-4 text-lg font-bold text-[#000000]">
                        Edit Pesanan #{{ editingOrder?.id }}
                    </h3>

                    <div class="space-y-4">
                        <!-- Status -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-semibold text-[#000000]"
                                >Status</label
                            >
                            <select
                                v-model="editForm.status"
                                class="w-full rounded-xl border border-[#dadad3] bg-[#f6f6f3] px-3 py-2.5 text-sm leading-[1.4] text-[#000000] focus:bg-white focus:ring-2 focus:ring-[#E22625] focus:outline-none"
                            >
                                <option
                                    v-for="st in allStatuses"
                                    :key="st"
                                    :value="st"
                                >
                                    {{ statusLabels[st] }}
                                </option>
                            </select>
                            <p
                                v-if="editForm.errors.status"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ editForm.errors.status }}
                            </p>
                        </div>

                        <!-- Items -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-[#000000]"
                                >Item Pesanan</label
                            >
                            <div
                                v-if="editItems.length === 0"
                                class="py-2 text-xs text-[#91918c]"
                            >
                                Tidak ada item. Simpan untuk menghapus semua
                                item.
                            </div>
                            <div v-else class="space-y-2">
                                <div
                                    v-for="item in editItems"
                                    :key="item.product_id"
                                    class="flex items-center gap-2 rounded-xl bg-[#f6f6f3] p-2"
                                >
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="truncate text-sm font-semibold text-[#000000]"
                                        >
                                            {{ item.name }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <button
                                            @click="
                                                item.quantity > 1
                                                    ? item.quantity--
                                                    : removeEditItem(
                                                          item.product_id,
                                                      )
                                            "
                                            class="flex h-6 w-6 items-center justify-center rounded-full border border-[#dadad3] text-xs font-bold text-[#000000] hover:bg-white"
                                        >
                                            -
                                        </button>
                                        <span
                                            class="w-5 text-center text-xs font-semibold"
                                            >{{ item.quantity }}</span
                                        >
                                        <button
                                            @click="item.quantity++"
                                            class="flex h-6 w-6 items-center justify-center rounded-full border border-[#dadad3] text-xs font-bold text-[#000000] hover:bg-white"
                                        >
                                            +
                                        </button>
                                    </div>
                                    <button
                                        @click="removeEditItem(item.product_id)"
                                        class="p-1 text-[#91918c] hover:text-red-500"
                                        title="Hapus item"
                                    >
                                        <svg
                                            class="h-3.5 w-3.5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p
                                v-if="editForm.errors.items"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ editForm.errors.items }}
                            </p>

                            <!-- Product Cards -->
                            <div class="mt-2">
                                <p
                                    class="mb-2 text-xs font-semibold text-[#62625b]"
                                >
                                    Pilih Produk
                                </p>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        v-for="p in products"
                                        :key="p.id"
                                        type="button"
                                        @click="addToEditItemsCard(p)"
                                        :disabled="
                                            editItems.some(
                                                (i) => i.product_id === p.id,
                                            )
                                        "
                                        class="flex flex-col items-center gap-1 rounded-xl border p-2 text-center transition-all disabled:cursor-not-allowed disabled:opacity-40"
                                        :class="
                                            editItems.some(
                                                (i) => i.product_id === p.id,
                                            )
                                                ? 'border-[#E22625] bg-red-50'
                                                : 'border-[#dadad3] bg-[#f6f6f3] hover:border-[#E22625] hover:shadow-sm'
                                        "
                                    >
                                        <div
                                            class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-lg bg-white"
                                        >
                                            <img
                                                v-if="p.image"
                                                :src="p.image"
                                                :alt="p.name"
                                                class="h-full w-full object-cover"
                                            />
                                            <svg
                                                v-else
                                                class="h-5 w-5 text-[#c8c8c1]"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                                />
                                            </svg>
                                        </div>
                                        <span
                                            class="line-clamp-2 text-[10px] leading-[1.3] font-semibold text-[#000000]"
                                            >{{ p.name }}</span
                                        >
                                        <span
                                            class="text-[10px] font-bold text-[#E22625]"
                                            >{{
                                                formatRupiah(currentPrice(p))
                                            }}</span
                                        >
                                        <span
                                            v-if="isOnDiscount(p)"
                                            class="text-[9px] text-[#91918c] line-through"
                                            >{{ formatRupiah(p.price) }}</span
                                        >
                                        <span
                                            v-if="
                                                editItems.some(
                                                    (i) =>
                                                        i.product_id === p.id,
                                                )
                                            "
                                            class="text-[9px] font-bold text-[#E22625]"
                                            >✓ Ditambahkan</span
                                        >
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-semibold text-[#000000]"
                                >Catatan</label
                            >
                            <textarea
                                v-model="editForm.notes"
                                rows="2"
                                placeholder="Catatan pesanan..."
                                class="w-full rounded-xl border border-[#dadad3] bg-[#f6f6f3] px-3 py-2 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:bg-white focus:ring-2 focus:ring-[#E22625] focus:outline-none"
                            />
                            <p
                                v-if="editForm.errors.notes"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ editForm.errors.notes }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            @click="showEditModal = false"
                            class="inline-flex h-9 items-center rounded-full px-5 text-sm font-semibold text-[#62625b] transition-colors hover:bg-[#f6f6f3]"
                        >
                            Batal
                        </button>
                        <button
                            @click="submitEdit"
                            :disabled="editForm.processing"
                            class="inline-flex h-9 items-center rounded-full bg-[#E22625] px-5 text-sm font-bold text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                        >
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
