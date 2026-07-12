# Tasks

- [x] Task 1: Isi firebaseConfig di useFirebase.ts dengan nilai dari Firebase Console
  - [x] SubTask 1.1: Dapatkan firebaseConfig dari google-services.json (projectId, apiKey, project_number, storageBucket)
  - [x] SubTask 1.2: Ganti placeholder values di `resources/js/composables/useFirebase.ts`
  - [ ] Catatan: `appId` masih placeholder (`YOUR_WEB_APP_ID`) — perlu register Web app di Firebase Console

- [x] Task 2: Ganti VAPID_KEY placeholder dengan VAPID_PUBLIC_KEY dari .env
  - [x] SubTask 2.1: Baca nilai `VAPID_PUBLIC_KEY` dari `.env` → `BCQFBT9kOFZiXT0MQbIPEfER3X3-HiGdQz5zgaU59pxnTNd7xNAyn3Gv1yKeK4ZbSUKrYHtAapMup3MwA1snL_Y`
  - [x] SubTask 2.2: Masukkan ke `VAPID_KEY` di `useFirebase.ts`

- [x] Task 3: Tambah VAPID vars ke .env.example
  - [x] SubTask 3.1: Tambah `VAPID_SUBJECT`, `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY` ke `.env.example`

- [x] Task 4: Build APK dan test
  - [x] SubTask 4.1: `npm run build` — ✅ sukses
  - [x] SubTask 4.2: `npx cap copy && npx cap sync android` — ✅ sukses
  - [ ] SubTask 4.3: Build APK di Android Studio + test di device (manual)
