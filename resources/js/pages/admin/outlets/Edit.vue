<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import Heading from '@/components/Heading.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Outlet', href: route('admin.outlets.index') },
            { title: 'Edit' },
        ] as BreadcrumbItem[],
    },
});

const { outlet, kasirs } = defineProps<{
    outlet: {
        id: number;
        name: string;
        address: string | null;
        phone: string | null;
        gallery: string[] | null;
        is_active: boolean;
        user_id: number | null;
        kasir: { id: number; name: string; email: string } | null;
    };
    kasirs: Array<{ id: number; name: string; email: string }>;
}>();

const form = useForm({
    name: outlet.name,
    address: outlet.address ?? '',
    phone: outlet.phone ?? '',
    gallery: outlet.gallery || ([] as string[]),
    is_active: outlet.is_active,
    user_id: outlet.user_id ?? null,
});

const selectedKasir = ref<string>(outlet.user_id?.toString() ?? '0');
const isDragging = ref(false);
const uploadQueue = ref<Set<number>>(new Set());

function submit() {
    form.user_id = selectedKasir.value !== '0' ? Number(selectedKasir.value) : null;
    form.put(route('admin.outlets.update', outlet.id));
}

function removePhoto(index: number) {
    (form.gallery as string[]).splice(index, 1);
}

async function uploadFile(file: File) {
    const idx = (form.gallery as string[]).length;
    (form.gallery as string[]).push(''); // placeholder
    uploadQueue.value.add(idx);

    const data = new FormData();
    data.append('file', file);

    try {
        const res = await fetch(route('admin.outlets.upload'), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '' },
            body: data,
        });
        const json = await res.json();
        (form.gallery as string[])[idx] = json.url;
    } catch {
        (form.gallery as string[]).splice(idx, 1);
    } finally {
        uploadQueue.value.delete(idx);
    }
}

function onDrop(e: DragEvent) {
    isDragging.value = false;
    if (!e.dataTransfer?.files.length) return;
    for (const file of e.dataTransfer.files) {
        if (file.type.startsWith('image/')) uploadFile(file);
    }
}

function onFileInput(e: Event) {
    const files = (e.target as HTMLInputElement).files;
    if (!files?.length) return;
    for (const file of files) {
        uploadFile(file);
    }
    (e.target as HTMLInputElement).value = '';
}
</script>

<template>
    <Head :title="`Edit ${outlet.name}`" />

    <Heading title="Edit Outlet" :description="outlet.name" />

    <Card class="max-w-lg">
        <CardHeader><CardTitle>Form Edit</CardTitle></CardHeader>
        <CardContent>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <Label for="name">Nama</Label>
                    <Input id="name" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>
                <div>
                    <Label for="address">Alamat</Label>
                    <Input id="address" v-model="form.address" />
                    <InputError :message="form.errors.address" />
                </div>
                <div>
                    <Label for="phone">Telepon</Label>
                    <Input id="phone" v-model="form.phone" />
                    <InputError :message="form.errors.phone" />
                </div>

                <!-- Gallery Drag & Drop -->
                <div class="space-y-2">
                    <Label>Galeri Foto</Label>
                    <InputError :message="form.errors.gallery" />

                    <!-- Thumbnails -->
                    <div v-if="form.gallery.length" class="grid grid-cols-3 gap-2">
                        <div
                            v-for="(url, idx) in form.gallery"
                            :key="idx"
                            class="group relative aspect-square overflow-hidden rounded-lg border border-[#dadad3]"
                        >
                            <template v-if="uploadQueue.has(idx)">
                                <div class="flex h-full items-center justify-center bg-[#f6f6f3]">
                                    <svg class="h-6 w-6 animate-spin text-[#91918c]" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                </div>
                            </template>
                            <template v-else>
                                <img :src="url" alt="" class="h-full w-full object-cover" />
                                <button
                                    type="button"
                                    @click="removePhoto(idx)"
                                    class="absolute right-1 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-black/60 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-red-600"
                                >
                                    ✕
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Drop Zone -->
                    <div
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="onDrop"
                        @click="($refs.fileInput as HTMLInputElement)?.click()"
                        :class="[
                            'flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-6 transition-colors',
                            isDragging
                                ? 'border-[#000000] bg-[#f6f6f3]'
                                : 'border-[#dadad3] hover:border-[#91918c]',
                        ]"
                    >
                        <svg class="mb-2 h-8 w-8 text-[#91918c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm text-[#62625b]">
                            <span class="font-semibold text-[#000000]">Seret & lepas</span> atau klik untuk upload
                        </p>
                        <p class="mt-1 text-xs text-[#91918c]">JPG, PNG, WEBP (max 5MB)</p>
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                            class="hidden"
                            @change="onFileInput"
                        />
                    </div>
                </div>

                <div>
                    <Label for="kasir">Kasir</Label>
                    <Select v-model="selectedKasir">
                        <SelectTrigger id="kasir">
                            <SelectValue placeholder="Pilih kasir..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="0">-- Tidak ada --</SelectItem>
                            <SelectItem v-for="k in kasirs" :key="k.id" :value="k.id.toString()">{{ k.name }} ({{ k.email }})</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.user_id" />
                </div>
                <div class="flex items-center gap-2">
                    <Checkbox id="active" v-model:checked="form.is_active" />
                    <Label for="active">Aktif</Label>
                </div>
                <Button type="submit" :disabled="form.processing">Simpan Perubahan</Button>
            </form>
        </CardContent>
    </Card>
</template>
