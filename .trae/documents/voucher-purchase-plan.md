# Rencana: Pembelian Voucher oleh Member

## Ringkasan

Sistem voucher saat ini hanya berfungsi setengah: Admin bisa buat voucher, tapi **member tidak bisa mendapatkan voucher sama sekali**. Tidak ada endpoint claim/beli voucher, tidak ada integrasi poin, tidak ada cara member menggunakan voucher di order. PRD menyebut "Poin dapat ditukarkan dengan reward atau voucher" tapi voucher belum diimplementasikan.

---

## Analisis Saat Ini

- **Admin**: Bisa create, lihat, hapus voucher ✅
- **Member**: Cuma lihat daftar voucher yang dimiliki (read-only) ❌
- **Route member voucher**: Cuma `GET /member/vouchers` — tidak ada POST/PUT
- **Order system**: Tidak ada relasi ke voucher — diskon voucher tidak bisa diaplikasikan
- **Tabel `vouchers`**: Tidak punya kolom `points_required` — tidak bisa ditukar dengan poin
- **Tabel `member_vouchers`**: Sudah punya `status` (active/used/expired) dan `redeemed_at` — siap dipakai

---

## Perubahan yang Diperlukan

### 1. Migration: Tambah `points_required` ke tabel `vouchers`

Agar voucher bisa dibeli dengan poin (seperti reward), perlu field `points_required`.

```php
Schema::table('vouchers', function (Blueprint $table) {
    $table->unsignedInteger('points_required')->nullable()->after('max_discount');
});
```

### 2. Controller: Tambah method `claim` di `Member\VoucherController`

Buat method baru (atau ubah `__invoke`) untuk handle claim voucher:

- **Route**: `POST /member/vouchers/{voucher}/claim` → `name: member.vouchers.claim`
- **Validasi**:
  - Voucher aktif (`is_active = true`)
  - Belum expired (`valid_from`/`valid_until`)
  - Member belum punya voucher ini yang masih active (optional — cegah duplicate)
  - Jika `points_required` diisi, cek saldo poin member cukup
- **Proses**:
  - Jika `points_required` ada: buat `PointTransaction` (type: redeem), kurangi poin member
  - Buat record `MemberVoucher` dengan `status: active`
- **Response**: Redirect back dengan flash success

### 3. Form Request (opsional): `ClaimVoucherRequest`

Buat FormRequest untuk validasi claim voucher, atau tetap inline di controller seperti style existing.

### 4. Route: Tambah `POST /member/vouchers/{voucher}/claim`

Masuk ke grup middleware `auth, verified, role:member` di `routes/web.php`.

### 5. Vue: Update halaman voucher member

**File**: `resources/js/pages/member/vouchers/Index.vue`

Saat ini halaman ini cuma menampilkan daftar voucher yang **sudah dimiliki**. Kita perlu dua tampilan:

**Tab/View 1: "Voucher Saya"** (existing — daftar `member_vouchers`)
- Tampilkan voucher yang sudah dimiliki dengan statusnya
- Tambah tombol "Gunakan" untuk apply ke order (nanti)

**Tab/View 2: "Tersedia"** (baru — daftar `vouchers` yang aktif & bisa diklaim)
- Tampilkan voucher yang tersedia untuk diklaim
- Setiap item: info diskon, poin yang dibutuhkan (jika ada), tombol "Klaim"
- Sembunyikan voucher yang sudah dimiliki member

### 6. Admin Create/Edit: Tambah field `points_required`

**File**: `resources/js/pages/admin/vouchers/Create.vue`
Tambah input `points_required` (nullable, number) di form create voucher.

### 7. Opsional: Apply voucher ke order

Ini lebih kompleks dan butuh perubahan signifikan di Order flow. **Tidak termasuk dalam rencana ini** — fokus dulu ke pembelian/klaim voucher.

---

## File yang Akan Diubah/Dibuat

| File | Tindakan | Keterangan |
|------|----------|------------|
| `database/migrations/xxxx_add_points_required_to_vouchers_table.php` | Buat | Tambah kolom `points_required` |
| `app/Http/Controllers/Member/VoucherController.php` | Edit | Ubah `__invoke` jadi method `index` + tambah method `claim` |
| `routes/web.php` | Edit | Tambah `POST /member/vouchers/{voucher}/claim` |
| `resources/js/pages/member/vouchers/Index.vue` | Edit | Tambah tab "Tersedia" dengan daftar voucher yang bisa diklaim + tombol klaim |
| `resources/js/pages/admin/vouchers/Create.vue` | Edit | Tambah field `points_required` |
| `app/Http/Controllers/Admin/VoucherController.php` | Edit | Tambah validasi `points_required` |

---

## Asumsi & Keputusan

| Asumsi/Keputusan | Detail |
|-----------------|--------|
| **Voucher dibeli dgn poin** | Ikut PRD: "Poin dapat ditukarkan dengan reward atau voucher". `points_required` nullable — gratis jika null |
| **Tidak ada konfirmasi** | Langsung klaim, tanpa step konfirmasi — ikut pola reward `redeem` |
| **Tidak ada anti-duplicate** | Sementara tidak cegah duplicate claim — bisa diubah nanti |
| **Tidak ada apply ke order** | Fokus klaim dulu. Apply voucher ke order butuh redesign Order system |

---

## Verifikasi

1. `php artisan migrate` — field `points_required` muncul
2. Admin buat voucher dengan `points_required`
3. Buka halaman voucher member — tab "Tersedia" muncul
4. Klik "Klaim" — voucher masuk ke "Voucher Saya"
5. Jika pake poin: poin berkurang sesuai `points_required`
6. `php artisan test` — semua test passing
