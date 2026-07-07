<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import Heading from '@/components/Heading.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Outlet' },
        ] as BreadcrumbItem[],
    },
});

const { outlets } = defineProps<{
    outlets: Array<{
        id: number;
        name: string;
        address: string | null;
        phone: string | null;
        is_active: boolean;
    }>;
}>();

const form = useForm({});

function destroy(id: number) {
    if (confirm('Hapus outlet ini?')) {
        form.delete(route('admin.outlets.destroy', id));
    }
}
</script>

<template>
    <Head title="Kelola Outlet" />

    <div class="mb-4 flex items-center justify-between">
        <Heading title="Kelola Outlet" description="Daftar outlet / cabang warung" />
        <Button as="child">
            <Link :href="route('admin.outlets.create')">+ Tambah</Link>
        </Button>
    </div>

    <div v-if="outlets.length === 0" class="py-8 text-center text-muted-foreground">
        Belum ada outlet.
    </div>

    <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <Card v-for="outlet in outlets" :key="outlet.id">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <CardTitle>{{ outlet.name }}</CardTitle>
                    <Badge :variant="outlet.is_active ? 'default' : 'secondary'">
                        {{ outlet.is_active ? 'Aktif' : 'Nonaktif' }}
                    </Badge>
                </div>
            </CardHeader>
            <CardContent class="space-y-2">
                <p class="text-sm text-muted-foreground">{{ outlet.address ?? 'Alamat belum diisi' }}</p>
                <p class="text-sm text-muted-foreground">{{ outlet.phone ?? 'Telepon belum diisi' }}</p>
                <div class="flex gap-2 pt-2">
                    <Button variant="outline" size="sm" as="child">
                        <Link :href="route('admin.outlets.edit', outlet.id)">Edit</Link>
                    </Button>
                    <Button variant="destructive" size="sm" @click="destroy(outlet.id)" :disabled="form.processing">Hapus</Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
