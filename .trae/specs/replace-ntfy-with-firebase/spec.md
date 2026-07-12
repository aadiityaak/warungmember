# Ganti Ntfy dengan Firebase Push Notification Spec

## Why
Ntfy (self-hosted push notification) akan diganti dengan Firebase Cloud Messaging (FCM) sebagai solusi push notification standar untuk Android dan web. FCM lebih reliable, didukung native oleh Android, dan menghilangkan kebutuhan self-hosted ntfy server.

## What Changes
- Hapus semua kode, konfigurasi, dan file terkait ntfy (**BREAKING**)
- Tambahkan Firebase Cloud Messaging (FCM) untuk push notification
- **BREAKING**: `ntfy_topic` dan `ntfy_token` dihapus dari `push_subscriptions`, diganti `fcm_token`
- **BREAKING**: `delivery_log` field berubah dari `ntfy_success`/`ntfy_failed` menjadi `fcm_success`/`fcm_failed`
- Hapus docker-compose ntfy service
- Hapus `useNtfy.ts` composable, ganti dengan `useFirebase.ts`
- Hapus dokumentasi ntfy di `.trae/documents/`

## Impact
- Affected specs: push notification
- Affected code: 
  - `docker-compose.yml`
  - `.env.example`
  - `config/services.php`
  - `database/migrations/` (migration baru)
  - `app/Models/PushSubscription.php`
  - `app/Http/Controllers/Member/PushSubscriptionController.php`
  - `app/Http/Controllers/Member/OrderController.php`
  - `app/Http/Controllers/Admin/BroadcastController.php`
  - `app/Jobs/SendPushNotification.php`
  - `resources/js/composables/useNtfy.ts` (dihapus)
  - `resources/js/composables/useFirebase.ts` (baru)
  - `resources/js/pages/member/notifications/Index.vue`
  - `resources/js/pages/admin/broadcasts/Index.vue`
  - `.trae/documents/integrasi-ntfy.md` (dihapus)
  - `.trae/documents/fix-notification-skeleton.md` (dihapus/update)
  - `.trae/specs/fix-notification-subscribed-status/` (dihapus)
  - `AGENTS.md` (hapus referensi ntfy)

## ADDED Requirements

### Requirement: Firebase Cloud Messaging Token Registration
Sistem HARUS menerima dan menyimpan FCM token dari client untuk mengirim push notification.

#### Scenario: Client mengirim FCM token saat subscribe
- **WHEN** client memanggil `POST /member/push/subscribe` dengan `fcm_token`
- **THEN** sistem menyimpan `fcm_token` di `push_subscriptions` untuk member tersebut

#### Scenario: Client subscribe tanpa FCM token
- **WHEN** client memanggil `POST /member/push/subscribe` tanpa `fcm_token`
- **THEN** sistem tetap membuat subscription record dengan `subscribed = true`

### Requirement: Firebase Push Notification Delivery
Sistem HARUS mengirim push notification melalui Firebase Admin SDK (FCM HTTP v1 API) menggunakan service account credentials.

#### Scenario: Kirim push notifikasi ke member yang subscribed
- **WHEN** `SendPushNotification` job dijalankan
- **THEN** sistem mengirim notifikasi ke FCM token milik member
- **AND** delivery log mencatat `fcm_success` atau `fcm_failed`

#### Scenario: Member tidak punya FCM token
- **WHEN** `SendPushNotification` job dijalankan untuk member tanpa FCM token
- **THEN** skip pengiriman push untuk member tersebut (hanya in-app notification)

### Requirement: Client-Side Firebase Initialization
Client HARUS menginisialisasi Firebase, meminta FCM token, dan mengirimkannya ke backend.

#### Scenario: User membuka halaman notifikasi
- **WHEN** user membuka halaman notifikasi
- **THEN** client init Firebase, request permission notifikasi, dapatkan FCM token, kirim ke `/member/push/subscribe`

#### Scenario: User unsubscribe
- **WHEN** user klik nonaktifkan notifikasi
- **THEN** client panggil `/member/push/unsubscribe` dan hapus FCM token lokal

#### Scenario: Firebase tidak didukung (unsupported browser)
- **WHEN** browser/device tidak mendukung Firebase Messaging
- **THEN** UI tidak menampilkan opsi push notification (sembunyikan section)

## MODIFIED Requirements

### Requirement: PushSubscription Model
Model `PushSubscription` HARUS menggunakan `fcm_token` sebagai field untuk menyimpan token device, menggantikan `ntfy_topic` dan `ntfy_token`.

#### Scenario: Membuat subscription baru
- **WHEN** subscription dibuat
- **THEN** field `fcm_token` terisi (nullable), `subscribed = true`, `platform = 'web'` atau `'android'`

### Requirement: Broadcast Delivery Log
`delivery_log` pada model `Broadcast` HARUS menggunakan key `fcm_success` dan `fcm_failed`, menggantikan `ntfy_success` dan `ntfy_failed`.

#### Scenario: Broadcast dibuat
- **WHEN** broadcast baru dibuat dengan type notification
- **THEN** `delivery_log` berisi `{total_push_attempts: 0, fcm_success: 0, fcm_failed: 0}`

#### Scenario: Admin melihat statistik delivery
- **WHEN** admin melihat daftar broadcast
- **THEN** UI menampilkan jumlah `fcm_success` dan `fcm_failed`

## REMOVED Requirements

### Requirement: Ntfy Self-Hosted Server
**Reason**: Diganti dengan Firebase Cloud Messaging
**Migration**: Hapus docker-compose ntfy service, hapus env `NTFY_SERVER_URL` dan `NTFY_TOPIC_SECRET`

### Requirement: Ntfy SSE Connection
**Reason**: Diganti dengan Firebase onMessage handler
**Migration**: Hapus file `useNtfy.ts`, buat `useFirebase.ts` baru

### Requirement: Ntfy Topic-Based Subscription
**Reason**: Firebase menggunakan device token, bukan topic per user
**Migration**: Hapus kolom `ntfy_topic` dan `ntfy_token`, tambahkan `fcm_token`
