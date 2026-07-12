# Fix: Halaman Notifikasi Stuck Skeleton

## Summary

Halaman notifikasi di `Index.vue` selalu menampilkan skeleton loading (`ntfy.loading` stuck `true`). Tombol "Aktifkan" tidak pernah muncul, baik di browser PC maupun di APK Android.

## Analisis Akar Masalah

Setelah investigasi, **response endpoint `push/status` sudah bersih** (`{"subscribed":false,"topic":null,"server":"https://ntfy.wsd.my.id"}`). Ini berarti route PHP di server sudah benar.

Masalah ada di **frontend composable `useNtfy.ts`** — `checkStatus()` gagal atau stuck sebelum `loading` berubah menjadi `false`. Karena composable tidak punya logging/debug sama sekali, kita tidak tahu di titik mana error terjadi.

### Kemungkinan penyebab:

1. **`route('member.push.status')` tidak terdefinisi di Ziggy** di halaman notifikasi. Walaupun route sudah ada di server dan `ziggy:generate` sudah dijalankan, ada kemungkinan Inertia page request mengembalikan Ziggy routes yang *berbeda* dengan yang ada di `route:list`. Jika `route()` throw error, `catch` block harusnya jalan dan `loading` jadi `false` — tapi error bisa saja terjadi di luar try/catch.

2. **`fetch` Inertia intercept** — di beberapa versi Inertia, `fetch` dari dalam komponen Inertia bisa di-intercept oleh Inertia router, mengubah response menjadi Inertia redirect/HTML alih-alih JSON. `res.json()` gagal.

3. **`Promise.race` dengan timeout** — timeout 3 detik bisa terlalu pendek jika koneksi lambat. Tapi harusnya tetap masuk `catch` → `finally` → `loading = false`.

## Rencana Perbaikan (Three-Prong)

### 1. Hardcode URL fetch — hilangkan ketergantungan `route()` Ziggy

File: `resources/js/composables/useNtfy.ts`

Ganti semua `route('member.push.status')`, `route('member.push.subscribe')`, `route('member.push.unsubscribe')` dengan string URL absolut `/member/push/status`, `/member/push/subscribe`, `/member/push/unsubscribe`. Ini menghilangkan kemungkinan Ziggy error.

### 2. Tambah `console.log` debug di `checkStatus()` & `subscribe()`

Sehingga kita bisa lihat di console browser di step mana fungsi stuck atau error. Log setiap: mulai, respon status, error, finally.

### 3. Gunakan `fetch` dengan `window.location.origin` prefix untuk kompatibilitas WebView

Di Capacitor WebView, relative URL bisa gagal karena WebView tidak selalu resolve relative path dengan benar.

## File yang Diubah

### `resources/js/composables/useNtfy.ts`

**What**: Hardcode URL, tambah debug log, tambah `window.location.origin` prefix  
**Why**: Memastikan fetch selalu mengarah ke endpoint yang benar, menghilangkan ketergantungan Ziggy, bisa dilacak di console  
**How**: 

```ts
const BASE = window.location.origin || '';

// checkStatus():
const url = `${BASE}/member/push/status`;
console.log('[useNtfy] checkStatus — fetching', url);
const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
console.log('[useNtfy] checkStatus — response', res.status, res.ok);
const data = await res.json();
console.log('[useNtfy] checkStatus — data', data);

// subscribe():
const url = `${BASE}/member/push/subscribe`;
// ... similar pattern

// unsubscribe():
const url = `${BASE}/member/push/unsubscribe`;
```

## Verifikasi

1. Buka halaman notifikasi di browser PC
2. Buka Console (F12) — lihat log `[useNtfy]`
3. Log harus menunjukkan: fetching → response 200 → data {...} → skeleton hilang → tombol "Aktifkan" muncul
4. Jika error, log akan menunjukkan di step mana
5. Setelah fix dikonfirmasi, rebuild dan deploy ke server
