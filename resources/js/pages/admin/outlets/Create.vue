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
            { title: 'Outlet', href: route('admin.outlets.index') },
            { title: 'Tambah' },
        ] as BreadcrumbItem[],
    },
});

const form = useForm({
    name: '',
    address: '',
    phone: '',
    gallery: [] as string[],
    is_active: true,
});

function submit() {
    form.post(route('admin.outlets.store'));
}

function addPhoto() {
    (form.gallery as string[]).push('');
}

function removePhoto(index: number) {
    (form.gallery as string[]).splice(index, 1);
}
</script>

<template>
    <Head title="Tambah Outlet" />

    <Heading title="Tambah Outlet" description="Tambahkan outlet / cabang baru" />

    <Card class="max-w-lg">
        <CardHeader><CardTitle>Form Outlet</CardTitle></CardHeader>
        <CardContent>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <Label for="name">Nama</Label>
                    <Input id="name" v-model="form.name" placeholder="Nama outlet" />
                    <InputError :message="form.errors.name" />
                </div>
                <div>
                    <Label for="address">Alamat</Label>
                    <Input id="address" v-model="form.address" placeholder="Alamat lengkap" />
                    <InputError :message="form.errors.address" />
                </div>
                <div>
                    <Label for="phone">Telepon</Label>
                    <Input id="phone" v-model="form.phone" placeholder="08xxx" />
                    <InputError :message="form.errors.phone" />
                </div>

                <!-- Gallery -->
                <div class="space-y-2">
                    <Label>Galeri Foto</Label>
                    <template v-for="(url, idx) in form.gallery" :key="idx">
                        <div class="flex gap-2">
                            <Input v-model="form.gallery[idx]" :placeholder="`URL foto ${idx + 1}`" />
                            <Button type="button" variant="outline" size="sm" @click="removePhoto(idx)">
                                ✕
                            </Button>
                        </div>
                    </template>
                    <InputError :message="form.errors.gallery" />
                    <Button type="button" variant="outline" size="sm" @click="addPhoto">
                        + Tambah Foto
                    </Button>
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
