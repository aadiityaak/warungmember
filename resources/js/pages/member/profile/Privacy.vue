<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';

defineOptions({ layout: MemberLayout });
</script>

<template>
    <Head title="Kebijakan Privasi" />

    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-xl font-bold leading-[1.2] text-[#000000]">Kebijakan Privasi</h1>
            <p class="mt-1 text-sm leading-[1.4] text-[#62625b]">Cara kami menjaga dan mengelola data kamu</p>
        </div>

        <div class="flex flex-col gap-5 text-sm leading-[1.6] text-[#333333]">
            <section>
                <h2 class="text-base font-semibold text-[#000000] mb-2">1. Informasi yang Kami Kumpulkan</h2>
                <p>
                    Kami mengumpulkan informasi yang kamu berikan saat mendaftar dan menggunakan WarungMember, termasuk nama,
                    alamat email, nomor telepon, tanggal lahir, dan foto profil. Kami juga mencatat riwayat transaksi,
                    poin, deposit, dan interaksimu dengan aplikasi untuk meningkatkan pengalaman pengguna.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-[#000000] mb-2">2. Penggunaan Informasi</h2>
                <p>
                    Data yang kami kumpulkan digunakan untuk: memproses transaksi dan poin loyalitas, mengirimkan notifikasi
                    terkait pesanan dan reward, meningkatkan layanan dan pengalaman pengguna, serta mengirimkan informasi
                    promo dan pembaruan yang relevan (dapat kamu nonaktifkan kapan saja).
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-[#000000] mb-2">3. Penyimpanan Data</h2>
                <p>
                    Data kamu disimpan di server yang aman dengan perlindungan enkripsi. Kami menyimpan data selama akunmu
                    aktif. Jika kamu menghapus akun, data akan dihapus dalam waktu maksimal 30 hari, kecuali data yang wajib
                    kami simpan untuk kepatuhan hukum.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-[#000000] mb-2">4. Berbagi Data</h2>
                <p>
                    Kami tidak menjual data pribadi kamu kepada pihak ketiga. Data hanya dibagikan kepada mitra merchant
                    tempat kamu bertransaksi sebatas yang diperlukan untuk memproses pesanan. Kami dapat membagikan data
                    jika diwajibkan oleh hukum atau untuk melindungi hak dan keamanan kami.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-[#000000] mb-2">5. Keamanan</h2>
                <p>
                    Kami menerapkan langkah-langkah keamanan teknis dan organisasional untuk melindungi data dari akses,
                    perubahan, pengungkapan, atau penghancuran yang tidak sah. Ini termasuk enkripsi data, kontrol akses
                    ketat, dan pemantauan berkala.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-[#000000] mb-2">6. Hak Kamu</h2>
                <p>
                    Kamu berhak untuk: mengakses dan mengunduh data pribadi yang kami simpan, meminta koreksi data yang
                    tidak akurat, meminta penghapusan akun dan data terkait, serta menarik persetujuan pemrosesan data
                    kapan saja. Hubungi kami melalui WhatsApp CS untuk menggunakan hak-hak ini.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-[#000000] mb-2">7. Perubahan Kebijakan</h2>
                <p>
                    Kami dapat memperbarui kebijakan privasi ini sewaktu-waktu. Perubahan signifikan akan diberitahukan
                    melalui aplikasi atau email. Kami sarankan untuk meninjau halaman ini secara berkala.
                </p>
            </section>

            <p class="text-xs text-[#91918c] mt-2">
                Terakhir diperbarui: Juli 2025
            </p>
        </div>
    </div>
</template>
