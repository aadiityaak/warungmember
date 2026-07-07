<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import Heading from '@/components/Heading.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Voucher' },
        ] as BreadcrumbItem[],
    },
});

const { vouchers } = defineProps<{
    vouchers: Array<{
        id: number;
        code: string;
        type: string;
        discount_type: string;
        discount_value: number;
        max_discount: number | null;
        is_active: boolean;
    }>;
}>();

const form = useForm({});

const typeLabels: Record<string, string> = {
    birthday: 'Ulang Tahun',
    golden_hour: 'Golden Hour',
    manual: 'Manual',
};

function destroy(id: number) {
    if (confirm('Hapus voucher ini?')) {
        form.delete(route('admin.vouchers.destroy', id));
    }
}
</script>

<template>
    <Head title="Kelola Voucher" />

    <div class="mb-4 flex items-center justify-between">
        <Heading title="Kelola Voucher" description="Daftar voucher promo" />
        <Button as="child">
            <Link :href="route('admin.vouchers.create')">+ Tambah</Link>
        </Button>
    </div>

    <div v-if="vouchers.length === 0" class="py-8 text-center text-muted-foreground">
        Belum ada voucher.
    </div>

    <table v-else class="w-full text-sm">
        <thead>
            <tr class="border-b text-left">
                <th class="pb-2 font-medium text-muted-foreground">Kode</th>
                <th class="pb-2 font-medium text-muted-foreground">Tipe</th>
                <th class="pb-2 font-medium text-muted-foreground">Diskon</th>
                <th class="pb-2 font-medium text-muted-foreground">Status</th>
                <th class="pb-2 font-medium text-muted-foreground">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="v in vouchers" :key="v.id" class="border-b last:border-0">
                <td class="py-2.5 font-mono">{{ v.code }}</td>
                <td class="py-2.5">{{ typeLabels[v.type] ?? v.type }}</td>
                <td class="py-2.5">{{ v.discount_type === 'percent' ? `${v.discount_value}%` : `Rp ${v.discount_value.toLocaleString('id-ID')}` }}</td>
                <td class="py-2.5">
                    <Badge :variant="v.is_active ? 'default' : 'secondary'">
                        {{ v.is_active ? 'Aktif' : 'Nonaktif' }}
                    </Badge>
                </td>
                <td class="py-2.5">
                    <Button variant="destructive" size="sm" @click="destroy(v.id)" :disabled="form.processing">Hapus</Button>
                </td>
            </tr>
        </tbody>
    </table>
</template>
