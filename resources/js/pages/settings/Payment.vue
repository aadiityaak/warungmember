<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { edit } from '@/routes/payment';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Payment settings', href: edit() },
        ],
    },
});

interface Bank {
    enabled: boolean;
    bank_name: string;
    account_number: string;
    account_name: string;
}

const { payment } = defineProps<{
    payment: {
        qris: {
            enabled: boolean;
            merchant_name: string;
            merchant_id: string;
            qr_image: string | null;
        };
        banks: Bank[];
    };
}>();

const form = useForm({
    qris_enabled: payment.qris.enabled,
    qris_merchant_name: payment.qris.merchant_name,
    qris_merchant_id: payment.qris.merchant_id,
    qris_qr_image: payment.qris.qr_image ?? '',
    qris_qr_file: null as File | null,
    banks: (payment.banks.length > 0 ? payment.banks : [{ enabled: true, bank_name: '', account_number: '', account_name: '' }]) as Bank[],
});

const qrPreview = ref<string | null>(payment.qris.qr_image ?? null);
const qrDragOver = ref(false);

const bankNames = computed(() => {
    const common = ['BCA', 'Mandiri', 'BNI', 'BRI', 'BSI', 'CIMB Niaga', 'Danamon', 'Permata', 'Maybank', 'Panin', 'OCBC NISP', 'BTN', 'Mega', 'BJB', 'BPD'];
    const used = form.banks.map((b) => b.bank_name).filter(Boolean);
    return common.filter((n) => !used.includes(n));
});

function addBank() {
    form.banks.push({ enabled: true, bank_name: '', account_number: '', account_name: '' });
}

function removeBank(i: number) {
    form.banks.splice(i, 1);
}

function handleQrDrop(e: DragEvent) {
    const file = e.dataTransfer?.files?.[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) return;
    if (file.size > 2048 * 1024) { alert('Maks 2MB.'); return; }
    form.qris_qr_file = file;
    qrPreview.value = URL.createObjectURL(file);
}

function handleQrInput(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    if (file.size > 2048 * 1024) { alert('Maks 2MB.'); return; }
    form.qris_qr_file = file;
    qrPreview.value = URL.createObjectURL(file);
}

function removeQr() {
    form.qris_qr_file = null;
    form.qris_qr_image = '';
    qrPreview.value = null;
}

function submit() {
    const payload: Record<string, unknown> = {
        qris: {
            enabled: form.qris_enabled,
            merchant_name: form.qris_merchant_name,
            merchant_id: form.qris_merchant_id,
            qr_image: form.qris_qr_image || null,
        },
        banks: form.banks,
    };

    form.transform(() => {
        const fd = new FormData();
        fd.append('_method', 'PUT');
        fd.append('qris[enabled]', payload.qris.enabled ? '1' : '0');
        fd.append('qris[merchant_name]', payload.qris.merchant_name);
        fd.append('qris[merchant_id]', payload.qris.merchant_id);
        fd.append('qris[qr_image]', payload.qris.qr_image ?? '');
        if (form.qris_qr_file) {
            fd.append('qris[qr_file]', form.qris_qr_file);
        }
        (payload.banks as Bank[]).forEach((b, i) => {
            fd.append(`banks[${i}][enabled]`, b.enabled ? '1' : '0');
            fd.append(`banks[${i}][bank_name]`, b.bank_name);
            fd.append(`banks[${i}][account_number]`, b.account_number);
            fd.append(`banks[${i}][account_name]`, b.account_name);
        });
        return fd;
    }).post(route('payment.update'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Payment" />

    <Heading
        title="Payment"
        description="Atur metode pembayaran QRIS dan transfer bank."
    />

    <form @submit.prevent="submit" class="space-y-8">
        <!-- QRIS -->
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <h3 class="text-base font-semibold text-[#000000]">QRIS</h3>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input
                        type="checkbox"
                        v-model="form.qris_enabled"
                        class="peer sr-only"
                    />
                    <div class="h-6 w-11 rounded-full bg-[#dadad3] after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-[#E22625] peer-checked:after:translate-x-full" />
                </label>
            </div>

            <template v-if="form.qris_enabled">
                <div>
                    <Label for="qris_merchant_name">Nama Merchant</Label>
                    <Input
                        id="qris_merchant_name"
                        v-model="form.qris_merchant_name"
                        placeholder="Warung Member"
                    />
                    <InputError :message="form.errors['qris.merchant_name']" />
                </div>

                <div>
                    <Label for="qris_merchant_id">Merchant ID</Label>
                    <Input
                        id="qris_merchant_id"
                        v-model="form.qris_merchant_id"
                        placeholder="ID123456789"
                    />
                    <InputError :message="form.errors['qris.merchant_id']" />
                </div>

                <div>
                    <Label>QR Code Image</Label>
                    <div
                        class="mt-1.5"
                        :class="{ 'ring-2 ring-[#E22625] ring-offset-2 rounded-2xl': qrDragOver }"
                        @dragenter.prevent="qrDragOver = true"
                        @dragleave.prevent="qrDragOver = false"
                        @dragover.prevent="qrDragOver = true"
                        @drop.prevent="qrDragOver = false; handleQrDrop($event)"
                    >
                        <label
                            class="flex cursor-pointer flex-col items-center gap-2 rounded-2xl border-2 border-dashed border-[#dadad3] bg-[#fbfbf9] px-6 py-6 transition-colors hover:border-[#91918c]"
                        >
                            <svg class="h-6 w-6 text-[#91918c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-sm leading-[1.4] text-[#62625b]">
                                <span class="font-semibold text-[#E22625]">Klik untuk upload</span> atau drag &amp; drop
                            </p>
                            <p class="text-xs leading-[1.4] text-[#91918c]">PNG, JPG, WebP — Maks 2MB</p>
                            <input
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="handleQrInput($event)"
                            />
                        </label>
                    </div>
                    <InputError :message="form.errors['qris.qr_file']" />
                    <InputError :message="form.errors['qris.qr_image']" />

                    <div v-if="qrPreview" class="mt-3 flex items-center gap-4 p-3 rounded-xl bg-[#f6f6f3]">
                        <img :src="qrPreview" alt="QR preview" class="h-24 w-24 object-contain border border-[#dadad3] rounded-lg" />
                        <button type="button" @click="removeQr" class="text-xs font-semibold text-[#E22625] hover:underline">
                            Hapus
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <Separator />

        <!-- Bank Transfer -->
        <div class="space-y-4">
            <h3 class="text-base font-semibold text-[#000000]">Transfer Bank</h3>

            <div v-for="(bank, i) in form.banks" :key="i" class="rounded-xl border border-[#dadad3] p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-[#62625b]">Bank {{ i + 1 }}</span>
                    <div class="flex items-center gap-2">
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" v-model="bank.enabled" class="peer sr-only" />
                            <div class="h-6 w-11 rounded-full bg-[#dadad3] after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-[#E22625] peer-checked:after:translate-x-full" />
                        </label>
                        <button
                            type="button"
                            @click="removeBank(i)"
                            class="text-xs font-semibold text-red-500 hover:underline"
                        >
                            Hapus
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <Label :for="'bank_name_' + i">Nama Bank</Label>
                        <div class="relative">
                            <input
                                :id="'bank_name_' + i"
                                v-model="bank.bank_name"
                                list="bank-suggestions"
                                placeholder="BCA"
                                class="w-full rounded-xl border border-[#dadad3] bg-[#f6f6f3] px-3 py-2.5 text-sm leading-[1.4] text-[#000000] placeholder:text-[#91918c] focus:bg-white focus:ring-2 focus:ring-[#E22625] focus:outline-none"
                            />
                            <datalist id="bank-suggestions">
                                <option v-for="name in bankNames" :key="name" :value="name" />
                            </datalist>
                        </div>
                        <InputError :message="form.errors['banks.' + i + '.bank_name']" />
                    </div>
                    <div>
                        <Label :for="'account_number_' + i">No. Rekening</Label>
                        <Input
                            :id="'account_number_' + i"
                            v-model="bank.account_number"
                            placeholder="1234567890"
                        />
                        <InputError :message="form.errors['banks.' + i + '.account_number']" />
                    </div>
                    <div>
                        <Label :for="'account_name_' + i">Atas Nama</Label>
                        <Input
                            :id="'account_name_' + i"
                            v-model="bank.account_name"
                            placeholder="Warung Member"
                        />
                        <InputError :message="form.errors['banks.' + i + '.account_name']" />
                    </div>
                </div>
            </div>

            <Button type="button" variant="outline" @click="addBank">
                + Tambah Bank
            </Button>
        </div>

        <div class="flex items-center gap-3">
            <Button type="submit" :disabled="form.processing">
                Simpan Pembayaran
            </Button>
            <span v-if="form.recentlySuccessful" class="text-sm font-semibold text-green-600">
                Tersimpan!
            </span>
        </div>
    </form>
</template>
