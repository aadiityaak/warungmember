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
            { title: 'Reward' },
        ] as BreadcrumbItem[],
    },
});

const { rewards } = defineProps<{
    rewards: Array<{
        id: number;
        name: string;
        description: string | null;
        image: string | null;
        points_required: number;
        stock: number | null;
        is_active: boolean;
    }>;
}>();

const form = useForm({});

function destroy(id: number) {
    if (confirm('Hapus reward ini?')) {
        form.delete(route('admin.rewards.destroy', id));
    }
}
</script>

<template>
    <Head title="Kelola Reward" />

    <div class="mb-4 flex items-center justify-between">
        <Heading title="Kelola Reward" description="Daftar reward yang bisa ditukar member" />
        <Button as="child">
            <Link :href="route('admin.rewards.create')">+ Tambah</Link>
        </Button>
    </div>

    <div v-if="rewards.length === 0" class="py-8 text-center text-muted-foreground">
        Belum ada reward.
    </div>

    <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <Card v-for="reward in rewards" :key="reward.id">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <CardTitle>{{ reward.name }}</CardTitle>
                    <Badge :variant="reward.is_active ? 'default' : 'secondary'">
                        {{ reward.is_active ? 'Aktif' : 'Nonaktif' }}
                    </Badge>
                </div>
            </CardHeader>
            <CardContent class="space-y-2">
                <p class="text-sm text-muted-foreground">{{ reward.description ?? '-' }}</p>
                <div class="text-sm">
                    <span class="font-semibold text-orange-600">{{ reward.points_required }}</span> poin
                </div>
                <div v-if="reward.stock !== null" class="text-sm text-muted-foreground">Stok: {{ reward.stock }}</div>
                <div v-else class="text-sm text-muted-foreground">Stok: Tidak terbatas</div>
                <div class="flex gap-2 pt-2">
                    <Button variant="outline" size="sm" as="child">
                        <Link :href="route('admin.rewards.edit', reward.id)">Edit</Link>
                    </Button>
                    <Button variant="destructive" size="sm" @click="destroy(reward.id)" :disabled="form.processing">Hapus</Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
