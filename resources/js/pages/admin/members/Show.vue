<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Heading from '@/components/Heading.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, User } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Member', href: route('admin.members.index') },
            { title: 'Detail' },
        ] as BreadcrumbItem[],
    },
});

const { member } = defineProps<{ member: User }>();
</script>

<template>
    <Head :title="`Detail ${member.name}`" />

    <Heading :title="member.name" description="Detail member" />

    <div class="grid gap-4 md:grid-cols-2">
        <Card>
            <CardHeader>
                <CardTitle>Informasi Akun</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2">
                <div>
                    <span class="text-sm text-muted-foreground">Nama</span>
                    <p>{{ member.name }}</p>
                </div>
                <div>
                    <span class="text-sm text-muted-foreground">Email</span>
                    <p>{{ member.email }}</p>
                </div>
                <div>
                    <span class="text-sm text-muted-foreground">Terdaftar</span>
                    <p>{{ new Date(member.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Loyalitas</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2">
                <div>
                    <span class="text-sm text-muted-foreground">Total Poin</span>
                    <p class="text-lg font-bold">0</p>
                </div>
                <div>
                    <span class="text-sm text-muted-foreground">Saldo Deposit</span>
                    <p class="text-lg font-bold">Rp 0</p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
