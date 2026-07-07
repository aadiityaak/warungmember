<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';
import MemberLayout from '@/layouts/MemberLayout.vue';

defineOptions({
    layout: MemberLayout,
});

const { profile } = defineProps<{
    profile: {
        id: number;
        name: string;
        email: string;
        avatar: string | null;
        birth_date: string | null;
    };
}>();

const avatarPreview = ref<string | null>(profile.avatar ?? null);

const form = useForm({
    name: profile.name,
    email: profile.email,
    password: '',
    birth_date: profile.birth_date ?? '',
    avatar: null as File | null,
});

function onAvatarChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.avatar = file;
        avatarPreview.value = URL.createObjectURL(file);
    }
}

function removeAvatar() {
    form.avatar = null;
    avatarPreview.value = null;
}

function submit() {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('member.profile.update'));
}
</script>

<template>
    <Head title="Edit Profil" />

    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link :href="route('member.profile')" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f6f6f3]">
                <svg class="h-5 w-5 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </Link>
            <div>
                <h2 class="text-[22px] font-semibold leading-[1.25] text-[#000000]">
                    Edit Profil
                </h2>
                <p class="mt-0.5 text-sm leading-[1.4] text-[#62625b]">
                    Perbarui informasi akun kamu
                </p>
            </div>
        </div>

        <!-- Avatar Section -->
        <div class="flex flex-col items-center gap-3">
            <div class="relative">
                <img
                    v-if="avatarPreview"
                    :src="avatarPreview"
                    alt="Avatar"
                    class="h-24 w-24 rounded-full border-2 border-[#dadad3] object-cover"
                />
                <div
                    v-else
                    class="flex h-24 w-24 items-center justify-center rounded-full bg-[#f6f6f3]"
                >
                    <svg class="h-10 w-10 text-[#91918c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <div class="flex gap-2">
                <label class="inline-flex cursor-pointer items-center rounded-full bg-[#f6f6f3] px-4 py-1.5 text-sm font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#e5e5e0]">
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ avatarPreview ? 'Ganti' : 'Upload' }}
                    <input type="file" accept="image/*" class="hidden" @change="onAvatarChange" />
                </label>
                <button
                    v-if="avatarPreview"
                    type="button"
                    @click="removeAvatar"
                    class="inline-flex items-center rounded-full bg-[#f6f6f3] px-4 py-1.5 text-sm font-bold leading-[1] text-[#000000] transition-colors hover:bg-[#e60023] hover:text-white"
                >
                    Hapus
                </button>
            </div>
            <InputError :message="form.errors.avatar" />
        </div>

        <!-- Form -->
        <div class="rounded-2xl border border-[#dadad3] bg-white p-5">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="name" class="mb-1 block text-sm font-semibold leading-[1.4] text-[#000000]">Nama</label>
                    <input
                        id="name"
                        v-model="form.name"
                        class="w-full rounded-2xl border border-[#91918c] bg-white px-4 py-2.5 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:border-black focus:outline-none focus:ring-[3px] focus:ring-[#435ee5]/30"
                    />
                    <InputError :message="form.errors.name" />
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-semibold leading-[1.4] text-[#000000]">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="w-full rounded-2xl border border-[#91918c] bg-white px-4 py-2.5 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:border-black focus:outline-none focus:ring-[3px] focus:ring-[#435ee5]/30"
                    />
                    <InputError :message="form.errors.email" />
                </div>
                <div>
                    <label for="birth_date" class="mb-1 block text-sm font-semibold leading-[1.4] text-[#000000]">Tanggal Lahir</label>
                    <input
                        id="birth_date"
                        v-model="form.birth_date"
                        type="date"
                        class="w-full rounded-2xl border border-[#91918c] bg-white px-4 py-2.5 text-sm leading-[1.4] text-[#000000] focus:border-black focus:outline-none focus:ring-[3px] focus:ring-[#435ee5]/30"
                    />
                    <InputError :message="form.errors.birth_date" />
                </div>
                <div>
                    <label for="password" class="mb-1 block text-sm font-semibold leading-[1.4] text-[#000000]">Password (isi hanya jika ingin mengganti)</label>
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
