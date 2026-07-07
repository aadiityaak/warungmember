<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';
import Heading from '@/components/Heading.vue';
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

const { product } = defineProps<{
    product: {
        id: number;
        name: string;
        description: string | null;
        image: string | null;
        price: number;
        points_earned: number;
        is_active: boolean;
    };
}>();

const form = useForm({
    name: product.name,
    description: product.description ?? '',
    image: product.image ?? '',
    price: product.price,
    points_earned: product.points_earned,
    is_active: product.is_active,
});

function submit() {
    form.put(route('admin.products.update', product.id));
}
</script>

<template>
    <Head :title="`Edit ${product.name}`" />

    <Heading title="Edit Produk" :description="product.name" />

    <Card class="max-w-lg">
        <CardHeader><CardTitle>Form Edit</CardTitle></CardHeader>
        <CardContent>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <Label for="name">Nama</Label>
                    <Input id="name" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>
                <div>
                    <Label for="desc">Deskripsi</Label>
                    <Input id="desc" v-model="form.description" />
                    <InputError :message="form.errors.description" />
                </div>
                <div>
                    <Label for="image">URL Gambar</Label>
                    <Input id="image" v-model="form.image" />
                    <InputError :message="form.errors.image" />
                </div>
                <div>
                    <Label for="price">Harga (Rp)</Label>
                    <Input id="price" v-model="form.price" type="number" min="0" />
                    <InputError :message="form.errors.price" />
                </div>
                <div>
                    <Label for="points">Poin Didapat</Label>
                    <Input id="points" v-model="form.points_earned" type="number" min="0" />
                    <InputError :message="form.errors.points_earned" />
                </div>
                <div class="flex items-center gap-2">
                    <Checkbox id="active" v-model:checked="form.is_active" />
                    <Label for="active">Aktif</Label>
                </div>
                <Button type="submit" :disabled="form.processing">Simpan Perubahan</Button>
            </form>
        </CardContent>
    </Card>
</template>
