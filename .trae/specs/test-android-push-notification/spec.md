# Test Android Push Notification Spec

## Why
Push notification sudah migrasi dari ntfy ke Firebase. Client-side Firebase config masih placeholder. Perlu diisi dengan nilai nyata dari Firebase Console, lalu diuji di device Android.

## What Changes
- Isi `firebaseConfig` di `useFirebase.ts` dengan nilai dari Firebase Console
- Ganti `VAPID_KEY` placeholder di `useFirebase.ts` dengan `VAPID_PUBLIC_KEY` dari `.env`
- Update `.env.example` — tambahkan VAPID vars yang sudah ada di `.env`
- Proses build APK dan testing di device

## Impact
- Affected code: `resources/js/composables/useFirebase.ts`, `.env.example`
- Tidak ada perubahan backend atau database

## ADDED Requirements

### Requirement: Firebase Web Config Terisi
`firebaseConfig` di `useFirebase.ts` HARUS berisi nilai nyata dari Firebase Console, bukan placeholder.

#### Scenario: Config diisi
- **WHEN** developer mengisi `firebaseConfig` dengan nilai dari Firebase Console
- **THEN** Firebase init bisa konek ke project yang benar

### Requirement: VAPID Key Terisi
`VAPID_KEY` di `useFirebase.ts` HARUS berisi public VAPID key yang valid.

#### Scenario: VAPID key diisi
- **WHEN** developer mengganti `VAPID_KEY` placeholder dengan `VAPID_PUBLIC_KEY` dari `.env`
- **THEN** `getToken()` bisa request FCM token dari browser

### Requirement: Android APK Terbuild
APK harus di-build dengan Capacitor dan google-services.json yang valid.

#### Scenario: Build APK
- **WHEN** developer menjalankan build pipeline (vite build → npx cap copy → npx cap sync → Android Studio build)
- **THEN** APK terinstall dan Firebase terintegrasi

## Manual Steps (non-code)

### Mendapatkan Firebase Web Config
1. Buka [Firebase Console](https://console.firebase.google.com/) → Project **masmbull** → ⚙️ Project Settings
2. Di tab **General** → **Your apps** → Tambah app Web (jika belum ada)
3. Copy `firebaseConfig` object yang diberikan
4. Tempel ke `resources/js/composables/useFirebase.ts`

### VAPID Key
- `VAPID_PUBLIC_KEY` di `.env` sudah terisi. Copy value-nya ke `VAPID_KEY` di `useFirebase.ts`.
- Jika perlu regenerate: Firebase Console → Project Settings → Cloud Messaging → Web Push certificates → Generate Key Pair

### Build APK
```bash
cd g:\warungmember
npm run build
npx cap copy
npx cap sync
```
Lalu buka `android/` di Android Studio, Build → Build APK.

### Test Flow
1. Install APK di device Android
2. Buka halaman Notifikasi → tap **Aktifkan**
3. Cek log: `php artisan pail` — lihat request `POST /member/push/subscribe`
4. Kirim test broadcast dari admin panel
5. Atau kirim manual via Firebase Console → Cloud Messaging → Send test message
