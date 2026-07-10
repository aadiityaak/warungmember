# Rencana: Perbaikan Status Notifikasi "Aktif" Padahal Belum di Allow

## Ringkasan

Status notifikasi menunjukkan "Notifikasi Aktif" meskipun pengguna belum memberikan izin. Penyebab: `init()` di `MemberLayout` memanggil `subscribe()` secara otomatis saat permission masih `'default'`, yang langsung memunculkan dialog izin browser.

---

## Akar Masalah

Di [usePushNotification.ts](file:///d:\dev\warungmember\resources\js\composables\usePushNotification.ts#L69-L75):

```ts
async function init() {
    if (!supported) return;
    await checkStatus();
    if (Notification.permission === 'default') {
        await subscribe();  // <— INI MASALAHNYA
    }
}
```

### Alur yang terjadi:
1. `MemberLayout` mount → `init()` dipanggil
2. `checkStatus()` → `permission = 'default'` (belum pernah ditanya)
3. `subscribe()` → `requestPermission()` → browser tampilkan dialog
4. User klik "Allow" (karena penasaran, terpaksa, atau tidak sengaja)
5. Subscription berhasil → `subscribed = true`
6. User sadar dan matikan di pengaturan browser → `permission = 'denied'`
7. Tapi `checkStatus()` sebelumnya sudah menyetel `subscribed = true`

Fix sebelumnya (nambah `permission !== 'granted'` early return di `checkStatus`) membantu untuk kasus #7, tapi tidak menyelesaikan penyebab utama: **dialog izin muncul tanpa interaksi user**.

---

## Perubahan yang Diperlukan

### 1. `usePushNotification.ts` — Ubah `init()` jadi hanya cek status

**File:** [usePushNotification.ts](file:///d:\dev\warungmember\resources\js\composables\usePushNotification.ts#L69-L75)

**Akar masalah:** Baris `await subscribe()` di `init()` memicu `requestPermission()` tanpa diminta user.

**Fix:** Hapus panggilan `subscribe()` dari `init()`. `init()` hanya menjalankan `checkStatus()`. User harus klik tombol "Aktifkan" secara sadar.

```diff
  async function init() {
      if (!supported) return;
      await checkStatus();
-     if (Notification.permission === 'default') {
-         await subscribe();
-     }
  }
```

### 2. `MemberLayout.vue` — Hapus auto-init push notification

**File:** [MemberLayout.vue](file:///d:\dev\warungmember\resources\js\layouts\MemberLayout.vue#L19-L21)

**Alasan:** Dengan perubahan #1, `init()` di `MemberLayout` cuma ngecek status yang mungkin belum akurat karena SW belum siap. Tidak ada gunanya manggil `init()` di layout global — cek status cukup dilakukan di halaman yang membutuhkan (notifications page).

```diff
  onMounted(() => {
      cart.loadFromServer();
-     usePushNotification().init();
  });
```

### 3. `Index.vue` — Perbaiki `checkStatus()` dengan retry & SW ready

**File:** [Index.vue](file:///d:\dev\warungmember\resources\js\pages\member\notifications\Index.vue#L12)

**Alasan:** `checkStatus()` dijalankan sekali di `onMounted`. Jika SW belum siap saat itu, `subscribed` tetap `false` meskipun sebenarnya aktif. Untuk halaman yang secara khusus menampilkan status notifikasi, kita perlu memastikan status akurat.

**Fix:** Tambah retry logic di `checkStatus()` atau panggil ulang setelah SW siap.

Di `Index.vue`:
```diff
- onMounted(() => push.checkStatus());
+ onMounted(async () => {
+     // Tunggu SW benar-benar siap sebelum cek status
+     if ('serviceWorker' in navigator) {
+         try {
+             await navigator.serviceWorker.ready;
+         } catch { /* ignore */ }
+     }
+     await push.checkStatus();
+ });
```

### 4. Template `Index.vue` — Tambah loading state

**File:** [Index.vue](file:///d:\dev\warungmember\resources\js\pages\member\notifications\Index.vue#L100-L155)

Tambahkan state `loading` untuk hindari flash "Belum Aktif" yang tiba-tiba berubah.

```diff
  const push = usePushNotification();
+ const statusLoading = ref(true);
- onMounted(() => push.checkStatus());
+ onMounted(async () => {
+     if ('serviceWorker' in navigator) {
+         try { await navigator.serviceWorker.ready; } catch {}
+     }
+     await push.checkStatus();
+     statusLoading.value = false;
+ });
```

Di template, card notifikasi:
```diff
  <div v-if="push.supported" ...>
+     <div v-if="statusLoading" class="... skeleton ...">
+     <template v-else>
          ... existing card content ...
+     </template>
  </div>
```

### 5. Validasi tambahan — Subscription expired cleanup

Auto-hapus subscription dari server saat `subscribe()` gagal (misal endpoint expired). Ini sudah ditangani di `SendPushNotification` job, tapi perlu juga di sisi client: jika server return error subscription not found atau endpoint invalid, hapus dari server.

**TIDAK DITAMBAH SEKARANG** — terlalu kompleks untuk issue ini. Cukup handle di backend job.

---

## Files yang Diubah

| File | Perubahan |
|---|---|
| `resources/js/composables/usePushNotification.ts` | Hapus `subscribe()` dari `init()`, init hanya check status |
| `resources/js/layouts/MemberLayout.vue` | Hapus panggilan `usePushNotification().init()` |
| `resources/js/pages/member/notifications/Index.vue` | Tambah `await navigator.serviceWorker.ready` sebelum `checkStatus()`, tambah `statusLoading` state + skeleton |

---

## Verifikasi

1. Buka halaman member apa saja → **TIDAK** muncul dialog izin notifikasi
2. Buka `/member/notifications` → card tampil "Notifikasi Belum Aktif"
3. Klik "Aktifkan" → dialog izin muncul → klik Allow → card berubah "Notifikasi Aktif"
4. Klik "Aktifkan" → dialog izin muncul → klik Block → card tetap "Belum Aktif", teks "Izin notifikasi ditolak"
5. Matikan notifikasi di pengaturan browser → refresh → card "Belum Aktif"
