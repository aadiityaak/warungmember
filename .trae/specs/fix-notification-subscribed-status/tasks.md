# Tasks

- [x] Task 1: Perbaiki endpoint `status()` agar tidak mengembalikan `server` saat tidak subscribed
  - [x] Ubah `PushSubscriptionController::status()` — kembalikan `server: null` jika `$sub === null`
  - [x] Verifikasi dengan test

- [x] Task 2: Tambahkan skeleton/loading state pada section push notification di halaman notifikasi
  - [x] Baca `resources/js/pages/member/notifications/Index.vue`
  - [x] Bungkus section push notification (baris 105-158) dengan conditional `v-if="!ntfy.loading"`
  - [x] Tambahkan skeleton placeholder saat `ntfy.loading === true`
  - [x] Jalankan `npm run build` untuk kompilasi ulang

# Task Dependencies
- Task 2 bisa dikerjakan paralel dengan Task 1 (tidak ada dependency)
