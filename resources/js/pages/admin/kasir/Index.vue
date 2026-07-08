<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Kasir' },
        ] as BreadcrumbItem[],
    },
});

const { kasirs } = defineProps<{
    kasirs: Array<{
        id: number;
        name: string;
        email: string;
        created_at: string;
    }>;
}>();

const deleteForm = useForm({});

function destroy(id: number) {
    if (confirm('Hapus kasir ini?')) {
        deleteForm.delete(route('admin.kasir.destroy', id));
    }
}
</script>

<template>
    <Head title="Kelola Kasir" />

    <div class="mx-6 pt-6">
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Kelola Kasir
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                Daftar akun kasir Warung Mas Mbull
            </p>
        </header>

        <div class="mb-6 flex gap-2">
            <Button as="child">
                <Link :href="route('admin.kasir.create')">+ Tambah Kasir</Link>
            </Button>
            <Button as="child" variant="outline">
                <Link :href="route('admin.outlets.index')">Outlet</Link>
            </Button>
        </div>

        <div v-if="kasirs.length === 0" class="rounded-2xl bg-[#f6f6f3] py-16 text-center">
            <p class="text-sm leading-[1.4] text-[#62625b]">Belum ada kasir ditambahkan.</p>
        </div>

        <div v-else class="overflow-hidden rounded-2xl border border-[#dadad3] bg-white">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#dadad3]">
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Nama</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000]">Email</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] hidden sm:table-cell">Dibuat</th>
                        <th class="px-4 py-3 text-left text-sm font-bold leading-[1.4] text-[#000000] w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="kasir in kasirs"
                        :key="kasir.id"
                        class="border-b border-[#e5e5e0] last:border-0 transition-colors hover:bg-[#fbfbf9]"
                    >
                        <td class="px-4 py-3">
                            <p class="text-sm leading-[1.4] font-semibold text-[#000000]">{{ kasir.name }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm leading-[1.4] text-[#62625b]">{{ kasir.email }}</span>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="text-sm leading-[1.4] text-[#91918c]">{{ new Date(kasir.created_at).toLocaleDateString('id-ID') }}</span>
                        </td>
                        <td class="px-2 py-3">
                            <div class="flex gap-1">
                                <Link
                                    :href="route('admin.kasir.edit', kasir.id)"
                                    class="inline-flex h-8 items-center rounded-full bg-[#f6f6f3] px-3 text-xs font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#e5e5e0]"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="destroy(kasir.id)"
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
        </div>
    </div>
</template>
