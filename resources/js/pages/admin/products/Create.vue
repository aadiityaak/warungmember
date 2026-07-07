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
            { title: 'Tambah' },
        ] as BreadcrumbItem[],
    },
});

const form = useForm({
    name: '',
    description: '',
    image: '',
    price: 0,
    points_earned: 0,
    is_active: true,
});

function submit() {
    form.post(route('admin.products.store'));
}
</script>

<template>
    <Head title="Tambah Produk" />

    <Heading title="Tambah Produk" description="Tambahkan produk untuk program loyalitas" />

    <Card class="max-w-lg">
        <CardHeader><CardTitle>Form Produk</CardTitle></CardHeader>
        <CardContent>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <Label for="name">Nama</Label>
                    <Input id="name" v-model="form.name" placeholder="Nama produk" />
                    <InputError :message="form.errors.name" />
                </div>
                <div>
                    <Label for="desc">Deskripsi</Label>
                    <Input id="desc" v-model="form.description" placeholder="Deskripsi singkat" />
                    <InputError :message="form.errors.description" />
                </div>
                <div>
                    <Label for="image">URL Gambar</Label>
                    <Input id="image" v-model="form.image" placeholder="https://..." />
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
                <Button type="submit" :disabled="form.processing">Simpan</Button>
            </form>
        </CardContent>
    </Card>
</template>
