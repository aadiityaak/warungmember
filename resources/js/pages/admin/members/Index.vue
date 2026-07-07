<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import Heading from '@/components/Heading.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import type { User } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Member', href: route('admin.members.index') },
        ] as BreadcrumbItem[],
    },
});

const { members, filters } = defineProps<{
    members: { data: User[]; current_page: number; last_page: number; from: number; to: number; total: number };
    filters: { search?: string };
}>();

const form = useForm({
    search: filters.search ?? '',
});

function submit() {
    form.get(route('admin.members.index'), { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Manajemen Member" />

    <Heading title="Manajemen Member" description="Kelola data member WarungMember" />

    <div class="mb-4 flex items-center gap-3 mx-6">
        <form @submit.prevent="submit" class="flex-1">
            <Input v-model="form.search" placeholder="Cari nama atau email..." />
        </form>
        <Button as="child">
            <Link :href="route('admin.members.create')">+ Tambah</Link>
        </Button>
    </div>

    <Card>
        <CardHeader>
            <CardTitle>Daftar Member ({{ members.total }})</CardTitle>
        </CardHeader>
        <CardContent>
            <div v-if="members.data.length === 0" class="py-8 text-center text-muted-foreground">
                Belum ada member terdaftar.
            </div>
            <table v-else class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left">
                        <th class="pb-2 font-medium text-muted-foreground">Nama</th>
                        <th class="pb-2 font-medium text-muted-foreground">Email</th>
                        <th class="pb-2 font-medium text-muted-foreground">Tanggal Daftar</th>
                        <th class="pb-2 font-medium text-muted-foreground">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="member in members.data" :key="member.id" class="border-b last:border-0">
                        <td class="py-2.5">{{ member.name }}</td>
                        <td class="py-2.5 text-muted-foreground">{{ member.email }}</td>
                        <td class="py-2.5 text-muted-foreground">{{ new Date(member.created_at).toLocaleDateString('id-ID') }}</td>
                        <td class="py-2.5">
                            <Button variant="outline" size="sm" as="child">
                                <Link :href="route('admin.members.show', member.id)">Detail</Link>
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="members.last_page > 1" class="mt-4 flex items-center justify-between text-sm text-muted-foreground">
                <span>Menampilkan {{ members.from }}-{{ members.to }} dari {{ members.total }}</span>
                <div class="flex gap-1">
                    <Link
                        v-for="page in members.last_page"
                        :key="page"
                        :href="route('admin.members.index', { page, search: filters.search })"
                        :class="['rounded px-2.5 py-1 text-sm', page === members.current_page ? 'bg-orange-600 text-white' : 'hover:bg-gray-100']"
                    >
                        {{ page }}
                    </Link>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
