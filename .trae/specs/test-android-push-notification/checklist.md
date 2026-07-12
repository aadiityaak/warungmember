# Checklist

- [ ] `firebaseConfig` di `useFirebase.ts` sudah pakai nilai nyata (bukan placeholder)
- [ ] `VAPID_KEY` di `useFirebase.ts` sudah pakai `VAPID_PUBLIC_KEY` dari `.env`
- [ ] VAPID vars ditambahkan ke `.env.example`
- [ ] `npm run build` berhasil tanpa error
- [ ] `npx cap copy && npx cap sync` berhasil
- [ ] APK terbuild di Android Studio
- [ ] Subscribe notification berhasil di device (cek log `/member/push/subscribe`)
- [ ] Push notification masuk ke device saat broadcast dikirim
