<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import Heading from '@/components/Heading.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Kasir', href: route('admin.kasir.index') },
            { title: 'Tambah' },
        ] as BreadcrumbItem[],
    },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
});

function submit() {
    form.post(route('admin.kasir.store'));
}
</script>

<template>
    <Head title="Tambah Kasir" />

    <Heading title="Tambah Kasir" description="Buat akun kasir baru" />

    <Card class="max-w-lg">
        <CardHeader><CardTitle>Form Tambah</CardTitle></CardHeader>
        <CardContent>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <Label for="name">Nama</Label>
                    <Input id="name" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>
                <div>
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" v-model="form.email" />
                    <InputError :message="form.errors.email" />
                </div>
                <div>
                    <Label for="password">Password</Label>
                    <Input id="password" type="password" v-model="form.password" />
                    <InputError :message="form.errors.password" />
                </div>
                <Button type="submit" :disabled="form.processing">Simpan</Button>
            </form>
        </CardContent>
    </Card>
</template>
