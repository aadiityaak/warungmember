# Checklist

- [x] `firebaseConfig` di `useFirebase.ts` sudah pakai nilai nyata (apiKey, projectId, messagingSenderId, storageBucket)
- [ ] `firebaseConfig` appId masih placeholder — perlu register Web app di Firebase Console
- [x] `VAPID_KEY` di `useFirebase.ts` sudah pakai `VAPID_PUBLIC_KEY` dari `.env`
- [x] VAPID vars ditambahkan ke `.env.example`
- [x] `npm run build` berhasil tanpa error
- [x] `npx cap copy && npx cap sync android` berhasil
- [ ] APK terbuild di Android Studio
- [ ] Subscribe notification berhasil di device (cek log `/member/push/subscribe`)
- [ ] Push notification masuk ke device saat broadcast dikirim
