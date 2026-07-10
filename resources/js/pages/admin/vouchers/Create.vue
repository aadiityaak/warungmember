<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';
import Heading from '@/components/Heading.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Voucher', href: route('admin.vouchers.index') },
            { title: 'Tambah' },
        ] as BreadcrumbItem[],
    },
});

const form = useForm({
    code: '',
    type: 'manual' as string,
    discount_type: 'percent' as string,
    discount_value: 0,
    min_purchase: 0,
    max_discount: null as number | null,
    points_required: null as number | null,
    valid_from: '',
    valid_until: '',
    is_active: true,
});

function submit() {
    form.post(route('admin.vouchers.store'));
}
</script>

<template>
    <Head title="Tambah Voucher" />

    <Heading title="Tambah Voucher" description="Buat voucher promo baru" />

    <Card class="max-w-lg">
        <CardHeader><CardTitle>Form Voucher</CardTitle></CardHeader>
        <CardContent>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <Label for="code">Kode Voucher</Label>
                    <Input id="code" v-model="form.code" placeholder="PROMO20" />
                    <InputError :message="form.errors.code" />
                </div>
                <div>
                    <Label>Tipe</Label>
                    <Select v-model="form.type">
                        <SelectTrigger><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="manual">Manual</SelectItem>
                                <SelectItem value="birthday">Ulang Tahun</SelectItem>
                                <SelectItem value="golden_hour">Golden Hour</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.type" />
                </div>
                <div>
                    <Label>Tipe Diskon</Label>
                    <Select v-model="form.discount_type">
                        <SelectTrigger><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="percent">Persen (%)</SelectItem>
                                <SelectItem value="fixed">Nominal (Rp)</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.discount_type" />
                </div>
                <div>
                    <Label for="val">Nilai Diskon</Label>
                    <Input id="val" v-model="form.discount_value" type="number" min="1" />
                    <InputError :message="form.errors.discount_value" />
                </div>
                <div>
                    <Label for="max">Maksimal Diskon (opsional)</Label>
                    <Input id="max" v-model="form.max_discount" type="number" min="0" />
                    <InputError :message="form.errors.max_discount" />
                </div>
                <div>
                    <Label for="min">Minimal Pembelian</Label>
                    <Input id="min" v-model="form.min_purchase" type="number" min="0" />
                    <InputError :message="form.errors.min_purchase" />
                </div>
                <div>
                    <Label for="points">Poin Dibutuhkan (opsional)</Label>
                    <Input id="points" v-model="form.points_required" type="number" min="1" />
                    <InputError :message="form.errors.points_required" />
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
