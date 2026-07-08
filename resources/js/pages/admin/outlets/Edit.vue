<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import Heading from '@/components/Heading.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Outlet', href: route('admin.outlets.index') },
            { title: 'Edit' },
        ] as BreadcrumbItem[],
    },
});

const { outlet, kasirs } = defineProps<{
    outlet: {
        id: number;
        name: string;
        address: string | null;
        phone: string | null;
        is_active: boolean;
        user_id: number | null;
        kasir: { id: number; name: string; email: string } | null;
    };
    kasirs: Array<{ id: number; name: string; email: string }>;
}>();

const form = useForm({
    name: outlet.name,
    address: outlet.address ?? '',
    phone: outlet.phone ?? '',
    is_active: outlet.is_active,
    user_id: outlet.user_id ?? null,
});

const selectedKasir = ref<string>(outlet.user_id?.toString() ?? '0');

function submit() {
    form.user_id = selectedKasir.value !== '0' ? Number(selectedKasir.value) : null;
    form.put(route('admin.outlets.update', outlet.id));
}
</script>

<template>
    <Head :title="`Edit ${outlet.name}`" />

    <Heading title="Edit Outlet" :description="outlet.name" />

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
                    <Label for="address">Alamat</Label>
                    <Input id="address" v-model="form.address" />
                    <InputError :message="form.errors.address" />
                </div>
                <div>
                    <Label for="phone">Telepon</Label>
                    <Input id="phone" v-model="form.phone" />
                    <InputError :message="form.errors.phone" />
                </div>
                <div>
                    <Label for="kasir">Kasir</Label>
                    <Select v-model="selectedKasir">
                        <SelectTrigger id="kasir">
                            <SelectValue placeholder="Pilih kasir..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="0">-- Tidak ada --</SelectItem>
                            <SelectItem v-for="k in kasirs" :key="k.id" :value="k.id.toString()">{{ k.name }} ({{ k.email }})</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.user_id" />
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
