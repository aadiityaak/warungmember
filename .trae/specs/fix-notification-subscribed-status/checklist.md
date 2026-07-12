# Checklist

- [x] `PushSubscriptionController::status()` mengembalikan `server: null` saat user tidak subscribed
- [x] `PushSubscriptionController::status()` mengembalikan `server: "<url>"` saat user subscribed
- [x] Halaman notifikasi menampilkan skeleton/loading state saat `ntfy.loading === true`
- [x] Halaman notifikasi menampilkan "Notifikasi Belum Aktif" saat `subscribed === false`
- [x] Halaman notifikasi menampilkan "Notifikasi Aktif" saat `subscribed === true`
- [x] `npm run build` sukses tanpa error
