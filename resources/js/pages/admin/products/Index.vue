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
            { title: 'Produk' },
        ] as BreadcrumbItem[],
    },
});

const { products } = defineProps<{
    products: Array<{
        id: number;
        name: string;
        description: string | null;
        image: string | null;
        price: number;
        points_earned: number;
        is_active: boolean;
    }>;
}>();

const form = useForm({});

function destroy(id: number) {
    if (confirm('Hapus produk ini?')) {
        form.delete(route('admin.products.destroy', id));
    }
}
</script>

<template>
    <Head title="Kelola Produk" />

    <div class="mb-4 flex items-center justify-between">
        <Heading title="Kelola Produk" description="Daftar produk untuk program loyalitas" />
        <Button as="child">
            <Link :href="route('admin.products.create')">+ Tambah</Link>
        </Button>
    </div>

    <div v-if="products.length === 0" class="py-8 text-center text-muted-foreground">
        Belum ada produk.
    </div>

    <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <Card v-for="product in products" :key="product.id">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <CardTitle>{{ product.name }}</CardTitle>
                    <Badge :variant="product.is_active ? 'default' : 'secondary'">
                        {{ product.is_active ? 'Aktif' : 'Nonaktif' }}
                    </Badge>
                </div>
            </CardHeader>
            <CardContent class="space-y-2">
                <p class="text-sm text-muted-foreground">{{ product.description ?? '-' }}</p>
                <div class="text-sm">
                    <span class="font-semibold">Rp {{ product.price.toLocaleString('id-ID') }}</span>
                    <span class="text-muted-foreground"> &mdash; </span>
                    <span class="font-semibold text-orange-600">{{ product.points_earned }}</span> poin
                </div>
                <div v-if="product.image" class="text-sm text-muted-foreground truncate">{{ product.image }}</div>
                <div class="flex gap-2 pt-2">
                    <Button variant="outline" size="sm" as="child">
                        <Link :href="route('admin.products.edit', product.id)">Edit</Link>
                    </Button>
                    <Button variant="destructive" size="sm" @click="destroy(product.id)" :disabled="form.processing">Hapus</Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
