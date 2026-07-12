# Fix Notifikasi Status Subscribe Salah Spec

## Why
Halaman notifikasi menampilkan status "Notifikasi Aktif" (subscribed = true) padahal user belum pernah subscribe. API `/member/push/status` sudah mengembalikan `subscribed: false` dengan benar, tapi UI tidak merefleksikan state yang sebenarnya.

## What Changes
- Tambahkan skeleton/loading state pada section push notification agar tidak menampilkan status salah sebelum `checkStatus()` selesai
- Hapus pengembalian `server` dari endpoint `status()` ketika user tidak subscribed (mencegah kebocoran info & potensi bug)
- Pastikan state `subscribed` tidak bisa berubah ke `true` kecuali melalui response sukses dari `subscribe()`

## Impact
- Affected specs: notifikasi push
- Affected code: `resources/js/composables/useNtfy.ts`, `resources/js/pages/member/notifications/Index.vue`, `app/Http/Controllers/Member/PushSubscriptionController.php`

## MODIFIED Requirements

### Requirement: Push Notification Status Display
Sistem HARUS menampilkan status subscribe push notification yang akurat berdasarkan response dari API `/member/push/status`.

#### Scenario: User belum subscribe — tampil "Belum Aktif"
- **GIVEN** user membuka halaman notifikasi
- **WHEN** API `/member/push/status` mengembalikan `subscribed: false`
- **THEN** UI menampilkan "Notifikasi Belum Aktif" dengan tombol "Aktifkan"

#### Scenario: Loading state sebelum API response
- **GIVEN** user membuka halaman notifikasi
- **WHEN** `checkStatus()` masih berjalan (loading = true)
- **THEN** UI menampilkan skeleton/placeholder, BUKAN status "Aktif" atau "Belum Aktif"

#### Scenario: User sudah subscribe — tampil "Aktif"
- **GIVEN** user sudah subscribe push notification
- **WHEN** API `/member/push/status` mengembalikan `subscribed: true`
- **THEN** UI menampilkan "Notifikasi Aktif" dengan tombol "Nonaktifkan"

### Requirement: Status API Response
Endpoint `GET /member/push/status` HARUS hanya mengembalikan `server` ketika user benar-benar subscribed.

#### Scenario: User tidak subscribed
- **WHEN** `GET /member/push/status` dipanggil untuk user yang tidak subscribed
- **THEN** response mengembalikan `{subscribed: false, topic: null, server: null}`

#### Scenario: User subscribed
- **WHEN** `GET /member/push/status` dipanggil untuk user yang subscribed
- **THEN** response mengembalikan `{subscribed: true, topic: "<topic>", server: "<server_url>"}`
