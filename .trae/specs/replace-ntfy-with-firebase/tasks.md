# Tasks

- [x] Task 1: Hapus semua file dan konfigurasi ntfy
  - [x] SubTask 1.1: Hapus `resources/js/composables/useNtfy.ts`
  - [x] SubTask 1.2: Hapus service ntfy dari `docker-compose.yml` (lines 2-12, 15)
  - [x] SubTask 1.3: Hapus env `NTFY_SERVER_URL` dan `NTFY_TOPIC_SECRET` dari `.env.example`
  - [x] SubTask 1.4: Hapus konfigurasi `services.ntfy` dari `config/services.php`
  - [x] SubTask 1.5: Hapus `.trae/documents/integrasi-ntfy.md` dan `.trae/documents/fix-notification-skeleton.md`
  - [x] SubTask 1.6: Hapus `.trae/specs/fix-notification-subscribed-status/`
  - [x] SubTask 1.7: Hapus referensi `useNtfy.ts` dari `AGENTS.md` line 46

- [x] Task 2: Buat migration untuk ganti skema database
  - [x] SubTask 2.1: Buat migration baru untuk drop `ntfy_topic`, `ntfy_token` di `push_subscriptions`
  - [x] SubTask 2.2: Jalankan migration `php artisan migrate`

- [x] Task 3: Update PushSubscription model
  - [x] SubTask 3.1: Ganti `ntfy_topic`, `ntfy_token` dengan `fcm_token` di `$fillable`

- [x] Task 4: Update PushSubscriptionController
  - [x] SubTask 4.1: Ubah `subscribe()` menerima `fcm_token` dari request, simpan ke DB
  - [x] SubTask 4.2: Ubah response `subscribe()` — kembalikan `success` saja, bukan `topic`/`server`
  - [x] SubTask 4.3: Ubah `status()` — tidak kembalikan `topic`/`server` ntfy, cukup `subscribed`

- [x] Task 5: Update SendPushNotification job (backend FCM delivery)
  - [x] SubTask 5.1: Install `kreait/laravel-firebase` via composer
  - [x] SubTask 5.2: Ganti `sendNtfy()` dengan `sendFcm()` menggunakan Firebase Admin SDK
  - [x] SubTask 5.3: Ubah `delivery_log` keys dari `ntfy_success`/`ntfy_failed` ke `fcm_success`/`fcm_failed`
  - [x] SubTask 5.4: Tambahkan config Firebase di `config/services.php` (path ke service account JSON)
  - [x] SubTask 5.5: Tambahkan env `FIREBASE_CREDENTIALS` di `.env.example`

- [x] Task 6: Update BroadcastController
  - [x] SubTask 6.1: Ganti `ntfy_success`/`ntfy_failed` dengan `fcm_success`/`fcm_failed` di initial `delivery_log`
  - [x] SubTask 6.2: Ganti di method `resend()` juga

- [x] Task 7: Buat `useFirebase.ts` composable baru
  - [x] SubTask 7.1: Install Firebase JS SDK (`firebase`) via npm
  - [x] SubTask 7.2: Buat `resources/js/composables/useFirebase.ts` dengan:
    - Init Firebase app + messaging
    - Request permission & dapatkan FCM token
    - Kirim token ke `/member/push/subscribe`
    - Handle foreground messages via `onMessage`
    - Unsubscribe (hapus token + panggil `/member/push/unsubscribe`)
    - Gunakan `reactive()` untuk return value
    - SSG/SSR safe: lazy init Firebase (client-only)

- [x] Task 8: Update halaman notifikasi member
  - [x] SubTask 8.1: Ganti import `useNtfy` dengan `useFirebase` di `resources/js/pages/member/notifications/Index.vue`
  - [x] SubTask 8.2: Sesuaikan binding template — ganti `ntfy` jadi `push`

- [x] Task 9: Update halaman broadcast admin
  - [x] SubTask 9.1: Ganti `ntfy_success`/`ntfy_failed` dengan `fcm_success`/`fcm_failed` di TypeScript type definition dan template
  - [x] SubTask 9.2: Update label dari "ntfy" ke "FCM" di template

- [x] Task 10: Konfigurasi Android Capacitor untuk Firebase
  - [x] SubTask 10.1: Tambahkan `google-services.json` ke `android/app/` (placeholder)
  - [x] SubTask 10.2: Update `capacitor.config.json` — pastikan plugin PushNotifications tetap ada
  - [x] SubTask 10.3: Pastikan Firebase dependencies di `android/build.gradle` dan `android/app/build.gradle`

- [x] Task 11: Testing
  - [x] SubTask 11.1: Tidak ada test eksisting untuk `PushSubscriptionController` (simple CRUD, verified via code review)
  - [x] SubTask 11.2: Tidak ada test eksisting untuk `SendPushNotification` (job logic verified via code review)
  - [x] SubTask 11.3: Tidak ada test push notification di project; SQLite driver tidak tersedia di environment local

# Task Dependencies
- Task 2 depends on Task 1
- Task 3, 5, 7 can run in parallel
- Task 4 depends on Task 3
- Task 6 depends on Task 5
- Task 8 depends on Task 7
- Task 9 depends on Task 5
- Task 10 depends on Task 7
- Task 11 depends on all tasks above
