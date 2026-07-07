<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';

defineOptions({ layout: MemberLayout });
</script>

<template>
    <Head title="Syarat & Ketentuan" />

    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-xl font-bold leading-[1.2] text-[#000000]">Syarat & Ketentuan</h1>
            <p class="mt-1 text-sm leading-[1.4] text-[#62625b]">Aturan program loyalitas WarungMember</p>
        </div>

        <div class="flex flex-col gap-5 text-sm leading-[1.6] text-[#333333]">
            <section>
                <h2 class="text-base font-semibold text-[#000000] mb-2">1. Keanggotaan</h2>
                <p>
                    Program loyalitas WarungMember terbuka untuk seluruh pelanggan yang telah mendaftar dan memiliki akun
                    terverifikasi. Setiap anggota berhak mengumpulkan poin dan menikmati manfaat sesuai ketentuan yang berlaku.
                    Keanggotaan bersifat personal dan tidak dapat dipindahtangankan.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-[#000000] mb-2">2. Poin Loyalitas</h2>
                <p>
                    Poin diberikan untuk setiap transaksi pembelian yang berhasil dilakukan melalui aplikasi. Jumlah poin yang
                    diperoleh dihitung berdasarkan total nilai belanja. Poin hanya berlaku untuk transaksi dengan status
                    &ldquo;Selesai&rdquo;. Poin tidak dapat ditukarkan dengan uang tunai.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-[#000000] mb-2">3. Penukaran Poin</h2>
                <p>
                    Poin yang terkumpul dapat ditukarkan dengan reward atau voucher yang tersedia di katalog reward. Penukaran
                    bersifat final dan tidak dapat dibatalkan. WarungMember berhak mengubah katalog reward dan jumlah poin
                    yang diperlukan sewaktu-waktu tanpa pemberitahuan sebelumnya.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-[#000000] mb-2">4. Voucher</h2>
                <p>
                    Voucher yang diperoleh dari penukaran poin memiliki masa berlaku dan syarat penggunaan tertentu. Voucher
                    tidak dapat digabungkan dengan promo lain kecuali disebutkan secara tertulis. Voucher yang telah
                    kedaluwarsa tidak dapat diperpanjang atau ditukar kembali.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-[#000000] mb-2">5. Deposit</h2>
                <p>
                    Saldo deposit dapat digunakan untuk pembayaran transaksi. Deposit tidak dapat ditarik dalam bentuk tunai.
                    WarungMember tidak bertanggung jawab atas kehilangan saldo akibat kelalaian anggota dalam menjaga
                    kerahasiaan akun.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-[#000000] mb-2">6. Perubahan Ketentuan</h2>
                <p>
                    WarungMember berhak mengubah syarat dan ketentuan ini sewaktu-waktu. Perubahan akan diinformasikan
                    melalui aplikasi atau email terdaftar. Dengan tetap menggunakan layanan setelah perubahan, anggota
                    dianggap menyetujui ketentuan yang telah diperbarui.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-[#000000] mb-2">7. Pembatalan & Penghentian</h2>
                <p>
                    WarungMember berhak membatalkan atau menangguhkan keanggotaan jika ditemukan pelanggaran terhadap
                    ketentuan ini, termasuk namun tidak terbatas pada penyalahgunaan poin, kecurangan, atau tindakan yang
                    merugikan merchant.
                </p>
            </section>

            <p class="text-xs text-[#91918c] mt-2">
                Terakhir diperbarui: Juli 2025
            </p>
        </div>
    </div>
</template>
