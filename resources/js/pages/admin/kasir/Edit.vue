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
            { title: 'Edit' },
        ] as BreadcrumbItem[],
    },
});

const { kasir } = defineProps<{
    kasir: {
        id: number;
        name: string;
        email: string;
    };
}>();

const form = useForm({
    name: kasir.name,
    email: kasir.email,
    password: '',
});

function submit() {
    form.put(route('admin.kasir.update', kasir.id));
}
</script>

<template>
    <Head :title="`Edit ${kasir.name}`" />

    <Heading title="Edit Kasir" :description="kasir.name" />

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
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" v-model="form.email" />
                    <InputError :message="form.errors.email" />
                </div>
                <div>
                    <Label for="password">Password (kosongkan jika tidak diubah)</Label>
                    <Input id="password" type="password" v-model="form.password" />
                    <InputError :message="form.errors.password" />
                </div>
                <Button type="submit" :disabled="form.processing">Simpan Perubahan</Button>
            </form>
        </CardContent>
    </Card>
</template>
