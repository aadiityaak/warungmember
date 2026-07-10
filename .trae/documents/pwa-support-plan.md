# Rencana: PWA Support untuk WarungMember

## Ringkasan

Menambahkan Progressive Web App (PWA) support ke aplikasi WarungMember yang merupakan SPA berbasis Inertia + Vue 3 + Vite. Pengguna bisa menginstall aplikasi ke layar depan perangkat dan menggunakan beberapa fitur offline.

---

## Analisis Saat Ini

- **Stack frontend**: Vue 3 + Inertia v3 + Vite 8 + TypeScript + Tailwind CSS v4
- **Entry point**: `resources/js/app.ts` — belum ada service worker registration
- **Master layout**: `resources/views/app.blade.php` — belum ada `<link rel="manifest">`
- **Status PWA**: Belum ada sama sekali — tidak ada SW, manifest, icon PWA, atau konfigurasi terkait
- **Sudah ada**: `apple-touch-icon.png` dan `robots.txt` di `public/`
- **Plugin Vite**: Laravel plugin, Inertia plugin, Tailwind CSS plugin, Vue plugin, Wayfinder plugin — belum ada PWA plugin

---

## Perubahan yang Diperlukan

### 1. Install `vite-plugin-pwa`

Package `vite-plugin-pwa` adalah solusi standar untuk menambahkan PWA ke project Vite. Ia mengenerate service worker (via Workbox) dan web app manifest secara otomatis saat build.

```bash
npm install -D vite-plugin-pwa
```

### 2. Konfigurasi `vite-plugin-pwa` di `vite.config.ts`

Tambahkan plugin PWA dengan konfigurasi:
- **manifest**: name, short_name, description, theme_color, background_color, display (standalone), orientation, scope, start_url, icons (192x192, 512x512)
- **workbox**: strategi caching dasar untuk static assets, navigasi fallback ke `index.html` (karena SPA)
- **registerSW**: biarkan plugin mengenerate registration code, atau handle manual di `app.ts`
- **includeAssets**: favicon, apple-touch-icon, logo

### 3. Generate PWA Icons

Butuh icon:
- `pwa-192x192.png` — untuk manifest
- `pwa-512x512.png` — untuk manifest dan splash screen

Bisa generate dari logo yang sudah ada di `public/logo/`.

Tempatkan di `public/pwa-icons/` atau biarkan `vite-plugin-pwa` handle via `pwa-config.srcDir`.

### 4. Service Worker Registration

`vite-plugin-pwa` dengan opsi `registerSW: true` (default) akan auto-register SW di build bundle. Tapi untuk kontrol lebih (misal nanti ada toast "Update available"), bisa handle manual:

Di `resources/js/app.ts`, tambahkan:
- Import `registerSW` dari `virtual:pwa-register`
- Daftarkan dengan callback `onNeedRefresh` dan `onOfflineReady`

Atau simplest: biarkan plugin handle registration, cukup inject script via plugin.

### 5. Update Master Blade Layout

Di `resources/views/app.blade.php`:
- Tambahkan tag `<meta name="theme-color" content="...">`
- Tag manifest akan di-inject otomatis oleh `vite-plugin-pwa` di head
- Pastikan viewport meta tag sudah sesuai (kemungkinan sudah)

### 6. Update Type Declarations

Buat `resources/js/types/pwa.d.ts` untuk deklarasi module `virtual:pwa-register` jika menggunakan manual registration.

### 7. Build & Verify

- Jalankan `npm run build` — plugin akan generate `sw.js`, `workbox-*.js`, dan manifest di `public/build/`
- Verifikasi file yang dihasilkan
- Test lokal bisa via Chrome DevTools > Application > Manifest & Service Workers

---

## File yang Akan Diubah/Dibuat

| File | Tindakan | Keterangan |
|------|----------|------------|
| `package.json` | Edit | Tambah `vite-plugin-pwa` ke devDependencies |
| `vite.config.ts` | Edit | Tambah konfigurasi plugin PWA |
| `resources/views/app.blade.php` | Edit | Tambah meta theme-color |
| `public/pwa-icons/pwa-192x192.png` | Buat | Icon PWA 192x192 |
| `public/pwa-icons/pwa-512x512.png` | Buat | Icon PWA 512x512 |

**Tidak perlu buat file baru untuk manifest/SW** — semua digenerate otomatis oleh `vite-plugin-pwa`.

---

## Asumsi & Keputusan

| Asumsi/Keputusan | Detail |
|-----------------|--------|
| **Plugin** | Pakai `vite-plugin-pwa` — standar de facto untuk Vite PWA |
| **SW strategy** | Workbox `generateSW` — generateSW lebih simple, injectManifest untuk kontrol lebih. Karena app SPA standar, `generateSW` sudah cukup |
| **Caching** | Network-first untuk navigasi (karena Inertia SPA perlu fetch data), cache-first untuk static assets (CSS, JS, fonts, images) |
| **Icons** | Generate dari logo existing (`public/logo/`) |
| **Offline page** | Tidak perlu custom offline page dulu — fallback ke halaman kosong dengan pesan "Kamu sedang offline" |
| **Update prompt** | Tidak perlu toast update prompt dulu — skip waiting + auto-reload saat update |
| **Registration** | Biarkan `vite-plugin-pwa` handle auto-registration (default) — lebih simple |

---

## Verifikasi

1. `npm run build` berhasil tanpa error
2. File `sw.js` dan manifest ter-generate di `public/build/`
3. Akses app via browser, buka Chrome DevTools > Application > Manifest — semua field terisi
4. Service Worker terdaftar dan aktif
5. App bisa diinstall (ada tombol Install di address bar)
6. Lighthouse audit PWA passing untuk kriteria basic (installable)
