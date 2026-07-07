# PRD — WarungMember (Loyalty & Engagement Platform)

**Versi:** 1.0
**Tanggal:** 2026-07-07
**Stack:** Laravel 13, Inertia v3, Vue 3, Tailwind CSS v4, SQLite

---

## Ringkasan Produk

WarungMember adalah platform loyalitas dan engagement untuk pelanggan Warung Mas Mbull. Member mendapatkan poin dari pembelian, menukar poin dengan menu gratis, menerima promo ulang tahun & golden hour, melakukan deposit, serta menerima notifikasi promo. Pemilik memiliki dashboard admin untuk analisis data pelanggan dan manajemen member.

---

## Fitur 1 — Sistem Poin & Reward (Loyalitas)

### 1.1 Perolehan Poin

- Setiap transaksi pembelian minimal **Rp 10.000** mendapatkan **1 poin**.
- Poin dihitung otomatis oleh kasir saat transaksi diselesaikan.
- Konfigurasi: `nominal_per_point` dan `min_purchase_for_point` disimpan di tabel `configs` (adjustable admin).
- Riwayat poin tercatat sebagai mutasi (`point_transactions`) dengan tipe `earn` / `redeem` / `expire`.

### 1.2 Penukaran Poin (Reward)

- Admin menentukan menu reward (nama menu, poin yang dibutuhkan, gambar, deskripsi, status aktif).
- Member menukar poin di kasir atau melalui aplikasi (self-service).
- Saat penukaran, sistem memvalidasi kecukupan poin, lalu mengurangi poin member.
- Reward memiliki stok terbatas (opsional, `stock` nullable).

### 1.3 Model Data

```
members
  - id, user_id (FK), member_code (unique), total_points, created_at, updated_at

point_transactions
  - id, member_id (FK), type (earn|redeem|expire), amount, reference_type, reference_id, note, created_at

rewards
  - id, name, description, image, points_required, stock (nullable), is_active, created_at, updated_at

reward_redemptions
  - id, member_id (FK), reward_id (FK), points_spent, redeemed_at, status (pending|completed|cancelled)
```

---

## Fitur 2 — Promo & Voucher Eksklusif

### 2.1 Promo Ulang Tahun (Birthday Promo)

- Sistem mendeteksi member yang berulang tahun hari ini (berdasarkan `birth_date` di `members`).
- Otomatis generate voucher diskon (misal: 20%, maks Rp 20.000) yang berlaku H+0 s/d H+7 dari tanggal ulang tahun.
- Voucher dikirim via notifikasi dan dapat dilihat di halaman "Voucher Saya".

### 2.2 Promo Golden Hour

- Admin mengatur jam golden hour (misal: 14:00–16:00) dan hari berlaku.
- Selama golden hour, semua member mendapatkan diskon otomatis saat transaksi.
- Atau: voucher golden hour muncul otomatis di akun member pada jam tersebut.
- Konfigurasi: `golden_hour_start`, `golden_hour_end`, `golden_hour_days`, `golden_hour_discount_percent`.

### 2.3 Model Data

```
vouchers
  - id, code (unique), type (birthday|golden_hour|manual), discount_type (percent|fixed), discount_value, min_purchase, max_discount, valid_from, valid_until, is_active, created_at

member_vouchers
  - id, member_id (FK), voucher_id (FK), redeemed_at (nullable), status (active|used|expired), created_at

configs
  - key, value (JSON)
  - entries: birthday_discount_percent, birthday_discount_max, birthday_valid_days, golden_hour_start, golden_hour_end, golden_hour_days, golden_hour_discount_percent
```

---

## Fitur 3 — Deposit Member

### 3.1 Alur Deposit

- Member datang ke cabang, menyerahkan uang ke kasir.
- Kasir mencatat deposit di panel admin: pilih member → masukkan nominal → konfirmasi.
- Saldo member bertambah, mutasi deposit tercatat.

### 3.2 Penggunaan Saldo

- Saat transaksi, member bisa memilih bayar dengan saldo deposit.
- Jika saldo cukup, langsung dipotong. Jika tidak, bisa kombinasi dengan cash / QRIS.

### 3.3 Model Data

```
members (tambahan field)
  - deposit_balance (integer, default 0)

deposit_transactions
  - id, member_id (FK), type (topup|payment|refund), amount, reference_type, reference_id, note, created_at
```

---

## Fitur 4 — Engagement & Notifikasi

### 4.1 Pusat Notifikasi

- Halaman "Notifikasi" di aplikasi member menampilkan semua notifikasi.
- Notifikasi terbagi: **Promo**, **Voucher**, **Poin**, **Deposit**, **Umum**.
- Setiap notifikasi memiliki status `read` / `unread`.
- Badge counter di ikon lonceng untuk notifikasi belum dibaca.

### 4.2 Jenis Notifikasi

| Trigger             | Pesan                                                  |
| ------------------- | ------------------------------------------------------ |
| Poin bertambah      | "Anda mendapatkan X poin dari transaksi #INV-XXX"      |
| Poin ditukar        | "Anda menukar X poin untuk [nama reward]"              |
| Voucher ulang tahun | "Selamat ulang tahun! Voucher diskon 20% menanti Anda" |
| Voucher golden hour | "Golden Hour! Diskon 15% berlaku sekarang"             |
| Deposit diterima    | "Deposit Rp X berhasil ditambahkan ke saldo Anda"      |
| Promo baru          | "Promo baru: [nama promo]"                             |
| Voucher kadaluarsa  | "Voucher [kode] akan kadaluarsa besok"                 |

### 4.3 Broadcast WhatsApp (Admin)

- Admin bisa memilih member (individu / filter / semua) lalu mengirim broadcast.
- Sistem generate link WhatsApp klik-otomatis via `wa.me` API.
- Template pesan bisa dipilih / dikustom.

### 4.4 Model Data

```
notifications
  - id, member_id (FK, nullable), type, title, body, data (JSON), read_at (nullable), created_at
```

---

## Fitur 5 — Dashboard Admin (Fitur Pemilik)

### 5.1 Analisis Data Pelanggan

**Metrik Utama (Dashboard Overview):**

- Total member terdaftar
- Member aktif bulan ini (melakukan transaksi)
- Total poin beredar
- Total deposit tersimpan
- Voucher yang telah diredeem

**Pelanggan Paling Setia:**

- Top 10 member berdasarkan total transaksi (nominal & frekuensi)
- Top 10 member berdasarkan poin terbanyak
- Filter periode: minggu ini, bulan ini, tahun ini, kustom

**Produk Terlaris:**

- Top 10 menu paling sering dibeli member (perlu relasi ke tabel `orders` / `transactions`)
- Filter periode yang sama dengan di atas

### 5.2 Manajemen Member

- **Tabel Member:** daftar semua member dengan filter (nama, kode member, status, tanggal daftar).
- **Detail Member:** profil, riwayat transaksi, riwayat poin, riwayat deposit, voucher yang dimiliki.
- **Broadcast:** pilih satu/beberapa member, tulis pesan, redirect ke WhatsApp atau kirim notifikasi in-app.
- **Import/Export:** import member dari CSV, export data member ke CSV.

### 5.3 Manajemen Reward & Voucher

- CRUD reward (nama, poin, gambar, stok).
- CRUD voucher manual (selain otomatis birthday & golden hour).
- Lihat riwayat penukaran reward & voucher.

### 5.4 Manajemen Konfigurasi

- Ubah nilai: `nominal_per_point`, `min_purchase_for_point`, golden hour, diskon ulang tahun, dll.

---

## Arsitektur Aplikasi

### Role & Guard

| Role     | Deskripsi                  | Auth Guard            |
| -------- | -------------------------- | --------------------- |
| `admin`  | Pemilik / pengelola warung | Fortify session (web) |
| `member` | Pelanggan member           | Fortify session (web) |
| `kasir`  | Petugas kasir cabang       | Fortify session (web) |

Gunakan single `users` table dengan kolom `role` (enum: `admin`, `member`, `kasir`).

### Struktur Halaman

**Member:**

- `/dashboard` — overview poin, saldo, reward terbaru
- `/points` — riwayat poin
- `/rewards` — katalog reward & penukaran
- `/vouchers` — voucher saya
- `/deposit` — riwayat deposit
- `/notifications` — pusat notifikasi
- `/profile` — profil & pengaturan

**Admin:**

- `/admin/dashboard` — metrik overview
- `/admin/members` — manajemen member
- `/admin/members/{id}` — detail member
- `/admin/rewards` — manajemen reward
- `/admin/vouchers` — manajemen voucher
- `/admin/transactions` — riwayat transaksi
- `/admin/broadcast` — broadcast pesan
- `/admin/settings` — konfigurasi aplikasi

### API Routes (opsional, untuk mobile)

Prefix: `/api/v1/`

```
GET    /api/v1/member/points
GET    /api/v1/member/vouchers
POST   /api/v1/member/rewards/{reward}/redeem
GET    /api/v1/member/notifications
POST   /api/v1/member/notifications/{id}/read
```

---

## Prioritas Pengembangan (Fase)

| Fase       | Fitur                                                                       | Estimasi Sprints |
| ---------- | --------------------------------------------------------------------------- | ---------------- |
| **Fase 1** | Auth (admin & member), manajemen member dasar, dashboard admin metrik dasar | 2                |
| **Fase 2** | Poin & reward (earn, redeem, katalog reward, riwayat)                       | 2                |
| **Fase 3** | Deposit member (topup, bayar pakai saldo, riwayat)                          | 1                |
| **Fase 4** | Promo & voucher (birthday, golden hour, manual voucher)                     | 2                |
| **Fase 5** | Notifikasi & broadcast WhatsApp                                             | 1                |
| **Fase 6** | Dashboard admin lanjutan (analisis pelanggan setia, produk terlaris)        | 1                |

---

## Non-Fungsional

- **Responsif:** Mobile-first, dapat diakses via HP kasir & pemilik.
- **Tampilan per Role:** Tampilan **mobile-only** untuk role `member`. Tampilan **desktop** untuk role `admin` dan `kasir`.
- **Keamanan:** Semua endpoint admin dilindungi middleware `can:admin`. Member hanya akses data sendiri.
- **Offline-tolerant:** Transaksi poin/deposit tetap bisa dicatat kasir (tidak bergantung koneksi internet member). Notifikasi dikirim async via queue.
- **Audit trail:** Semua mutasi poin, deposit, dan penukaran tercatat lengkap dengan timestamp.
