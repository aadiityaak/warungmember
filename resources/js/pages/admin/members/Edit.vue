<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Member', href: route('admin.members.index') },
            { title: 'Edit' },
        ] as BreadcrumbItem[],
    },
});

const { member } = defineProps<{
    member: {
        id: number;
        name: string;
        email: string;
    };
}>();

const form = useForm({
    name: member.name,
    email: member.email,
    password: '',
});

function submit() {
    form.put(route('admin.members.update', member.id));
}
</script>

<template>
    <Head :title="`Edit ${member.name}`" />

    <div class="mx-6 pt-6">
        <header class="mb-6 space-y-0.5">
            <h2 class="text-[28px] font-bold leading-[1.2] tracking-[-1.2px] text-[#000000]">
                Edit Member
            </h2>
            <p class="text-sm leading-[1.4] text-[#62625b]">
                {{ member.name }}
            </p>
        </header>

        <div class="max-w-lg rounded-2xl border border-[#dadad3] bg-white p-8">
            <h3 class="mb-6 text-[18px] font-semibold leading-[1.3] text-[#000000]">Form Edit</h3>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-semibold leading-[1.4] text-[#000000]">Nama</label>
                    <input
                        id="name"
                        v-model="form.name"
                        class="w-full rounded-2xl border border-[#91918c] bg-white px-4 py-2.5 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:border-black focus:outline-none focus:ring-[3px] focus:ring-[#435ee5]/30"
                    />
                    <InputError :message="form.errors.name" />
                </div>
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold leading-[1.4] text-[#000000]">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="w-full rounded-2xl border border-[#91918c] bg-white px-4 py-2.5 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:border-black focus:outline-none focus:ring-[3px] focus:ring-[#435ee5]/30"
                    />
                    <InputError :message="form.errors.email" />
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-semibold leading-[1.4] text-[#000000]">Password (isi hanya jika ingin mengganti)</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="Minimal 8 karakter"
                        class="w-full rounded-2xl border border-[#91918c] bg-white px-4 py-2.5 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:border-black focus:outline-none focus:ring-[3px] focus:ring-[#435ee5]/30"
                    />
                    <InputError :message="form.errors.password" />
                </div>
                <Button type="submit" :disabled="form.processing" class="w-full">Simpan Perubahan</Button>
            </form>
        </div>
    </div>
</template>
