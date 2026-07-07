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
    is_active: true,
});

function submit() {
    form.post(route('admin.outlets.store'));
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
                <div class="flex items-center gap-2">
                    <Checkbox id="active" v-model:checked="form.is_active" />
                    <Label for="active">Aktif</Label>
                </div>
                <Button type="submit" :disabled="form.processing">Simpan</Button>
            </form>
        </CardContent>
    </Card>
</template>
