<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/branding';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Branding settings', href: edit() },
        ],
    },
});

const { branding } = defineProps<{
    branding: {
        app_name: string;
        logo_url: string | null;
        favicon_url: string | null;
        primary_color: string;
        whatsapp_number: string;
    };
}>();

const form = useForm({
    app_name: branding.app_name,
    logo_url: branding.logo_url ?? '',
    favicon_url: branding.favicon_url ?? '',
    primary_color: branding.primary_color,
    whatsapp_number: branding.whatsapp_number,
    logo_file: null as File | null,
    favicon_file: null as File | null,
});

const logoPreview = ref<string | null>(branding.logo_url ?? null);
const faviconPreview = ref<string | null>(branding.favicon_url ?? null);
const logoDragOver = ref(false);
const faviconDragOver = ref(false);

function handleFileDrop(
    e: DragEvent,
    target: 'logo' | 'favicon'
) {
    const file = e.dataTransfer?.files?.[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        return;
    }

    const maxSize = target === 'logo' ? 2048 : 512;

    if (file.size > maxSize * 1024) {
        alert(`Ukuran file maksimal ${target === 'logo' ? '2MB' : '512KB'}.`);
        return;
    }

    if (target === 'logo') {
        form.logo_file = file;
        logoPreview.value = URL.createObjectURL(file);
    } else {
        form.favicon_file = file;
        faviconPreview.value = URL.createObjectURL(file);
    }
}

function handleFileInput(
    e: Event,
    target: 'logo' | 'favicon'
) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;

    const maxSize = target === 'logo' ? 2048 : 512;

    if (file.size > maxSize * 1024) {
        alert(`Ukuran file maksimal ${target === 'logo' ? '2MB' : '512KB'}.`);
        return;
    }

    if (target === 'logo') {
        form.logo_file = file;
        logoPreview.value = URL.createObjectURL(file);
    } else {
        form.favicon_file = file;
        faviconPreview.value = URL.createObjectURL(file);
    }
}

function removeFile(target: 'logo' | 'favicon') {
    if (target === 'logo') {
        form.logo_file = null;
        form.logo_url = '';
        logoPreview.value = null;
    } else {
        form.favicon_file = null;
        form.favicon_url = '';
        faviconPreview.value = null;
    }
}

function submit() {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('branding.update'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Branding" />

    <Heading
        title="Branding"
        description="Atur nama aplikasi, logo, favicon, dan warna utama WarungMember."
    />

    <form @submit.prevent="submit" class="space-y-6">
        <div>
            <Label for="app_name">Nama Aplikasi</Label>
            <Input
                id="app_name"
                v-model="form.app_name"
                placeholder="WarungMember"
            />
            <InputError :message="form.errors.app_name" />
        </div>

        <!-- Logo Upload -->
        <div>
            <Label>Logo</Label>
            <div
                class="mt-1.5"
                :class="{
                    'ring-2 ring-[#E22625] ring-offset-2 rounded-2xl': logoDragOver,
                }"
                @dragenter.prevent="logoDragOver = true"
                @dragleave.prevent="logoDragOver = false"
                @dragover.prevent="logoDragOver = true"
                @drop.prevent="logoDragOver = false; handleFileDrop($event, 'logo')"
            >
                <label
                    class="flex cursor-pointer flex-col items-center gap-2 rounded-2xl border-2 border-dashed border-[#dadad3] bg-[#fbfbf9] px-6 py-8 transition-colors hover:border-[#91918c]"
                >
                    <svg class="h-8 w-8 text-[#91918c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm leading-[1.4] text-[#62625b]">
                        <span class="font-semibold text-[#E22625]">Klik untuk upload</span> atau drag &amp; drop
                    </p>
                    <p class="text-xs leading-[1.4] text-[#91918c]">PNG, JPG, SVG, WebP — Maks 2MB</p>
                    <input
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="handleFileInput($event, 'logo')"
                    />
                </label>
            </div>
            <InputError :message="form.errors.logo_file" />
            <InputError :message="form.errors.logo_url" />

            <!-- Logo Preview -->
            <div v-if="logoPreview" class="mt-3 flex items-center gap-3 p-3 rounded-xl bg-[#f6f6f3]">
                <img
                    :src="logoPreview"
                    alt="Logo preview"
                    class="h-12 object-contain max-w-[200px]"
                />
                <button
                    type="button"
                    @click="removeFile('logo')"
                    class="text-xs font-semibold text-[#E22625] hover:underline ml-auto"
                >
                    Hapus
                </button>
            </div>
        </div>

        <!-- Favicon Upload -->
        <div>
            <Label>Favicon</Label>
            <div
                class="mt-1.5"
                :class="{
                    'ring-2 ring-[#E22625] ring-offset-2 rounded-2xl': faviconDragOver,
                }"
                @dragenter.prevent="faviconDragOver = true"
                @dragleave.prevent="faviconDragOver = false"
                @dragover.prevent="faviconDragOver = true"
                @drop.prevent="faviconDragOver = false; handleFileDrop($event, 'favicon')"
            >
                <label
                    class="flex cursor-pointer flex-col items-center gap-2 rounded-2xl border-2 border-dashed border-[#dadad3] bg-[#fbfbf9] px-6 py-8 transition-colors hover:border-[#91918c]"
                >
                    <svg class="h-8 w-8 text-[#91918c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm leading-[1.4] text-[#62625b]">
                        <span class="font-semibold text-[#E22625]">Klik untuk upload</span> atau drag &amp; drop
                    </p>
                    <p class="text-xs leading-[1.4] text-[#91918c]">PNG, ICO, SVG — Maks 512KB (disarankan 32x32px)</p>
                    <input
                        type="file"
                        accept="image/*,.ico"
                        class="hidden"
                        @change="handleFileInput($event, 'favicon')"
                    />
                </label>
            </div>
            <InputError :message="form.errors.favicon_file" />
            <InputError :message="form.errors.favicon_url" />

            <!-- Favicon Preview -->
            <div v-if="faviconPreview" class="mt-3 flex items-center gap-3 p-3 rounded-xl bg-[#f6f6f3]">
                <img
                    :src="faviconPreview"
                    alt="Favicon preview"
                    class="h-8 w-8 object-contain"
                />
                <span class="text-sm text-[#62625b]">{{ form.favicon_file?.name ?? 'favicon' }}</span>
                <button
                    type="button"
                    @click="removeFile('favicon')"
                    class="text-xs font-semibold text-[#E22625] hover:underline ml-auto"
                >
                    Hapus
                </button>
            </div>
        </div>

        <!-- Primary Color -->
        <div>
            <Label for="primary_color">Warna Utama</Label>
            <div class="flex items-center gap-3 mt-1.5">
                <Input
                    id="primary_color"
                    v-model="form.primary_color"
                    placeholder="#E22625"
                    class="w-28"
                />
                <input
                    type="color"
                    :value="form.primary_color"
                    @input="form.primary_color = ($event.target as HTMLInputElement).value"
                    class="h-9 w-9 rounded-lg border border-[#dadad3] cursor-pointer p-0.5"
                />
            </div>
            <InputError :message="form.errors.primary_color" />
            <p class="text-xs leading-[1.4] text-[#91918c] mt-1">
                Warna utama digunakan untuk tombol, link, dan aksen di seluruh aplikasi.
            </p>

            <div class="mt-3 flex gap-2">
                <div
                    class="h-8 w-8 rounded-lg border border-[#dadad3]"
                    :style="{ backgroundColor: form.primary_color }"
                />
                <div
                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white"
                    :style="{ backgroundColor: form.primary_color }"
                >
                    Sample Button
                </div>
                <span
                    class="text-sm font-semibold"
                    :style="{ color: form.primary_color }"
                >Sample Link</span>
            </div>
        </div>


        <div>
            <Label for="whatsapp_number">WhatsApp Customer Service</Label>
            <Input
                id="whatsapp_number"
                v-model="form.whatsapp_number"
                placeholder="081335405231"
            />
            <InputError :message="form.errors.whatsapp_number" />
            <p class="text-xs leading-[1.4] text-[#91918c] mt-1">
                Nomor WhatsApp untuk customer service.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <Button type="submit" :disabled="form.processing">
                Simpan Branding
            </Button>
            <span
                v-if="form.recentlySuccessful"
                class="text-sm font-semibold text-green-600"
            >
                Tersimpan!
            </span>
        </div>
    </form>
</template>
