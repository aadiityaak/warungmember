---
name: "server-structure"
description: "Mengingat struktur server deployment: Laravel public/ dipetakan ke public_html/. Invoke saat deploy, upload build, atau membahas path server."
---

# Server Structure

## Flow Deploy

```
lokal: composer run build:production
  → php artisan build:package
  → output: dist/warungmember.1.0.0.zip
  → upload zip ke server
  → extract ke public_html/
  → buka https://domain.tld/install/
  → ikuti panduan instalasi
```

## Struktur Setelah Extract di public_html/

```
public_html/                     # DOCUMENT ROOT web server
├── build/                       # Hasil npm run build (flatten dari public/build/)
│   ├── assets/
│   ├── manifest.json
│   └── sw.js
├── install/                     # Installer web
│   ├── index.php
│   ├── cleanup.php
│   ├── debug.php
│   └── htaccess-template.txt
├── index.php                    # Entry point utama (dari deployment-index.php)
├── .env.example
├── README.txt
│
└── warungmember/                # Laravel backend directory
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/                  # Hanya untuk @vite manifest lookup
    │   ├── build/
    │   │   └── manifest.json    # BuildPackageCommand: createWarungmemberPublicDirectory()
    │   ├── index.php
    │   └── .htaccess
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    ├── artisan
    ├── composer.json
    └── .env.example
```

## Perintah Deploy

| Step | Command |
|---|---|
| Build package | `composer run build:production` |
| Output zip | `dist/warungmember.{version}.zip` |
| Upload | Upload zip ke server |
| Extract | Extract ke `public_html/` |
| Install | Buka `https://domain.tld/install/` |

## Sinkronisasi Manifest (build/manifest.json)

Setelah `build:package`, manifest.json ada di **dua tempat** dan keduanya **identik** karena berasal dari source yang sama (`public/build/`):

| Lokasi | Dihasilkan oleh | Fungsi |
|---|---|---|
| `build/manifest.json` | `flattenPublicStructure()` — isi `public/` dipindah ke root | Di-serve langsung oleh web server sebagai document root |
| `warungmember/public/build/manifest.json` | `createWarungmemberPublicDirectory()` — eksplisit copy manifest saja | Digunakan `@vite` untuk asset lookup di Laravel backend |

**Prosesnya:**
1. `npm run build` → output ke `public/build/` (manifest + assets)
2. `flattenPublicStructure()` → pindahkan seluruh `public/` ke root temp → `build/manifest.json` (root)
3. `createWarungmemberPublicDirectory()` → copy `manifest.json` ke `warungmember/public/build/`
4. Kedua manifest dari source yang sama → **selalu sinkron**

## Catatan Penting

- **Root `index.php`** di public_html/ adalah `deployment-index.php` yang di-copy oleh `BuildPackageCommand`. File ini mem-bootstrap Laravel dari folder `warungmember/`.
- **`warungmember/public/build/manifest.json`** hanya digunakan oleh `@vite` untuk asset lookup. Asset sebenarnya ada di root `build/`.
- **`.htaccess`** di root public_html/ akan di-generate oleh installer setelah instalasi selesai.
- Saat `npm run build` sendiri (tanpa build:package), output hanya ke `public/build/` lokal. Perlu `composer run build:production` untuk membuat zip deploy.
