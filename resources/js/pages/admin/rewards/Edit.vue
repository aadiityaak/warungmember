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
            { title: 'Reward', href: route('admin.rewards.index') },
            { title: 'Edit' },
        ] as BreadcrumbItem[],
    },
});

const { reward } = defineProps<{
    reward: {
        id: number;
        name: string;
        description: string | null;
        image: string | null;
        points_required: number;
        stock: number | null;
        is_active: boolean;
    };
}>();

const form = useForm({
    name: reward.name,
    description: reward.description ?? '',
    image: reward.image ?? '',
    points_required: reward.points_required,
    stock: reward.stock,
    is_active: reward.is_active,
});

function submit() {
    form.put(route('admin.rewards.update', reward.id));
}
</script>

<template>
    <Head :title="`Edit ${reward.name}`" />

    <Heading title="Edit Reward" :description="reward.name" />

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
                    <Label for="points">Poin Dibutuhkan</Label>
                    <Input id="points" v-model="form.points_required" type="number" min="1" />
                    <InputError :message="form.errors.points_required" />
                </div>
                <div>
                    <Label for="stock">Stok</Label>
                    <Input id="stock" v-model="form.stock" type="number" min="0" />
                    <InputError :message="form.errors.stock" />
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
