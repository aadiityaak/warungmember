# Tasks

- [ ] Task 1: Isi firebaseConfig di useFirebase.ts dengan nilai dari Firebase Console
  - [ ] SubTask 1.1: Dapatkan firebaseConfig dari Firebase Console (Project Settings → Your apps → Web app)
  - [ ] SubTask 1.2: Ganti placeholder values di `resources/js/composables/useFirebase.ts`

- [ ] Task 2: Ganti VAPID_KEY placeholder dengan VAPID_PUBLIC_KEY dari .env
  - [ ] SubTask 2.1: Baca nilai `VAPID_PUBLIC_KEY` dari `.env`
  - [ ] SubTask 2.2: Masukkan ke `VAPID_KEY` di `useFirebase.ts`

- [ ] Task 3: Tambah VAPID vars ke .env.example
  - [ ] SubTask 3.1: Tambah `VAPID_SUBJECT`, `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY` ke `.env.example`

- [ ] Task 4: Build APK dan test
  - [ ] SubTask 4.1: Jalankan `npm run build && npx cap copy && npx cap sync`
  - [ ] SubTask 4.2: Build APK di Android Studio
  - [ ] SubTask 4.3: Install di device, test subscribe & terima notifikasi

# Task Dependencies
- Task 1, 2 parallel
- Task 3 independent
- Task 4 depends on Task 1, 2
