<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    status?: string;
    canResetPassword?: boolean;
}>();

defineOptions({
    layout: {
        title: 'Masuk ke WarungMember',
        description: 'Lanjutkan untuk mengakses akun kamu.',
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Log in" />

    <!-- Status -->
    <div v-if="status" class="mb-5 rounded-2xl bg-[#c7f0da] px-4 py-3 text-sm font-medium text-[#103c25]">
        {{ status }}
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-4">
        <!-- Email -->
        <label class="flex flex-col gap-1.5">
            <span class="text-sm font-bold leading-[1.4] text-[#000000]">Email</span>
            <input
                v-model="form.email"
                type="email"
                required
                autocomplete="username"
                placeholder="nama@email.com"
                class="h-11 rounded-2xl border border-[#91918c] bg-white px-[15px] py-[11px] text-base leading-[1.4] text-[#000000] placeholder:text-[#91918c] outline-none transition-all focus:border-[#000000] focus:ring-[3px] focus:ring-[#435ee5]"
                :class="{ 'border-[#9e0a0a] ring-[3px] ring-[#9e0a0a]/20': form.errors.email }"
            />
            <p v-if="form.errors.email" class="text-xs leading-[1.4] text-[#9e0a0a]">{{ form.errors.email }}</p>
        </label>

        <!-- Password -->
        <label class="flex flex-col gap-1.5">
            <span class="text-sm font-bold leading-[1.4] text-[#000000]">Kata Sandi</span>
            <div class="relative">
                <input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    autocomplete="current-password"
                    placeholder="Masukkan kata sandi"
                    class="h-11 w-full rounded-2xl border border-[#91918c] bg-white px-[15px] py-[11px] pr-11 text-base leading-[1.4] text-[#000000] placeholder:text-[#91918c] outline-none transition-all focus:border-[#000000] focus:ring-[3px] focus:ring-[#435ee5]"
                    :class="{ 'border-[#9e0a0a] ring-[3px] ring-[#9e0a0a]/20': form.errors.password }"
                />
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-[#91918c] hover:text-[#000000] transition-colors"
                    :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                >
                    <svg v-if="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            <p v-if="form.errors.password" class="text-xs leading-[1.4] text-[#9e0a0a]">{{ form.errors.password }}</p>
        </label>

        <!-- Remember + Forgot -->
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input
                    v-model="form.remember"
                    type="checkbox"
                    class="h-5 w-5 rounded-[6px] border border-[#91918c] text-[#e60023] accent-[#e60023] focus:ring-[#435ee5]"
                />
                <span class="text-sm leading-[1.4] text-[#000000]">Ingat saya</span>
            </label>
            <Link
                v-if="canResetPassword"
                :href="route('password.request')"
                class="text-sm font-semibold leading-[1.4] text-[#211922] underline underline-offset-2 hover:text-[#000000]"
            >
                Lupa kata sandi?
            </Link>
        </div>

        <!-- Submit -->
        <button
            type="submit"
            :disabled="form.processing"
            class="mt-1 inline-flex h-10 w-full items-center justify-center rounded-2xl bg-[#e60023] px-4 text-sm font-bold text-white transition-colors hover:bg-[#cc001f] disabled:bg-[#f6f6f3] disabled:text-[#91918c]"
        >
            <svg
                v-if="form.processing"
                class="mr-2 h-4 w-4 animate-spin text-white"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            Masuk
        </button>
    </form>

    <!-- Divider -->
    <div class="my-6 flex items-center gap-3">
        <div class="h-px flex-1 bg-[#dadad3]" />
        <span class="text-xs leading-[1.4] text-[#91918c]">atau</span>
        <div class="h-px flex-1 bg-[#dadad3]" />
    </div>

    <!-- Sign up -->
    <p class="text-center text-sm leading-[1.4] text-[#62625b]">
        Belum punya akun?
        <Link
            :href="route('register')"
            class="font-semibold text-[#211922] underline underline-offset-2 hover:text-[#000000]"
        >
            Daftar di sini
        </Link>
    </p>
</template>
