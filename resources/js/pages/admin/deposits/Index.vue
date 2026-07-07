<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import Heading from '@/components/Heading.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Deposit' },
        ] as BreadcrumbItem[],
    },
});

const { members } = defineProps<{
    members: Array<{
        id: number;
        user: { name: string; email: string };
        deposit_balance: number;
    }>;
}>();

const form = useForm({
    member_id: 0,
    amount: 0,
});

function submit() {
    form.post(route('admin.deposits.store'));
}
</script>

<template>
    <Head title="Deposit" />

    <Heading title="Manajemen Deposit" description="Catat deposit member" />

    <div class="grid gap-6 md:grid-cols-2">
        <Card>
            <CardHeader><CardTitle>Top-up Deposit</CardTitle></CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <Label>Member</Label>
                        <Select v-model="form.member_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih member" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem v-for="m in members" :key="m.id" :value="m.id">
                                        {{ m.user.name }} — {{ m.user.email }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.member_id" />
                    </div>
                    <div>
                        <Label for="amount">Nominal</Label>
                        <Input id="amount" v-model="form.amount" type="number" min="1000" />
                        <InputError :message="form.errors.amount" />
                    </div>
                    <Button type="submit" :disabled="form.processing">Proses Deposit</Button>
                </form>
            </CardContent>
        </Card>

        <Card>
            <CardHeader><CardTitle>Saldo Member</CardTitle></CardHeader>
            <CardContent>
                <div v-if="members.length === 0" class="py-4 text-center text-muted-foreground">
                    Belum ada member.
                </div>
                <div v-else class="space-y-2">
                    <div v-for="m in members" :key="m.id" class="flex items-center justify-between border-b py-2 text-sm">
                        <div>
                            <p class="font-medium">{{ m.user.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ m.user.email }}</p>
                        </div>
                        <p class="font-semibold">Rp {{ m.deposit_balance.toLocaleString('id-ID') }}</p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
