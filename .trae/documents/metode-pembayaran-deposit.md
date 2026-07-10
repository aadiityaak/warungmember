# Rencana: Dukungan Pembayaran via Deposit/Saldo

## Ringkasan

Menambahkan `deposit` sebagai metode pembayaran baru di order (admin & member). Sistem deposit sudah ada (`deposit_balance` di tabel `members`, `deposit_transactions`), tapi belum terhubung ke pembuatan order.

---

## Analisis Saat Ini

- **Payment methods existing:** `cash`, `qris`, `transfer` — divalidasi via `in:cash,qris,transfer` di kedua controller
- **Deposit balance:** Tersimpan di `members.deposit_balance` (unsigned integer, default 0)
- **DepositTransaction:** Model & tabel siap, tipe `payment` sudah ada di enum tapi belum pernah dipakai
- **Frontend:** `paymentLabels` & `paymentMethods` hanya berisi 3 metode di semua halaman
- **Data member:** Admin orders page hanya load `member_code`, belum include `deposit_balance`

---

## Perubahan yang Dibutuhkan

### 1. Backend — Admin OrderController (`app/Http/Controllers/Admin/OrderController.php`)

**a. `index()` — Load deposit_balance**
- Ubah eager loading member jadi include `deposit_balance`
- `->with('member:id,user_id,member_code,deposit_balance')`

**b. `store()` — Validasi & proses deposit**
- Tambah `deposit` ke rule `payment_method`: `'required|in:cash,qris,transfer,deposit'`
- Dalam transaksi, sebelum create order:
  - Jika `payment_method === 'deposit'`:
    - Load `$member = $order->user->member`
    - Validasi `$member->deposit_balance >= $total`, throw error jika kurang
    - Set `paid_amount = $total`, `change = 0` di order
    - Kurangi `$member->decrement('deposit_balance', $total)`
    - Buat `DepositTransaction::create([...])` dengan `type = 'payment'`, `reference` ke order

### 2. Backend — Member OrderController (`app/Http/Controllers/Member/OrderController.php`)

**a. `store()` — Validasi & proses deposit**
- Tambah `deposit` ke rule: `'required|in:cash,qris,transfer,deposit'`
- Dalam transaksi: logika sama seperti admin (validasi balance, set paid_amount, decrement, create DepositTransaction)

**b. `index()` — Kirim balance member ke frontend (opsional, bisa via auth)**
- Pass `deposit_balance` member ke view Inertia

### 3. Frontend — Admin Orders Index (`resources/js/pages/admin/orders/Index.vue`)

**a. Props type**
- Tambah `deposit_balance: number` ke tipe `member`

**b. `paymentLabels` & `paymentMethods`**
- Tambah `deposit: 'Deposit'` ke `paymentLabels`
- Tambah `'deposit'` ke array `paymentMethods`

**c. UI create modal — bagian Payment Method**
- Jika `createForm.payment_method === 'deposit'`:
  - Tampilkan saldo member terpilih (dari data member)
  - Tampilkan pesan error jika saldo < total
  - Tidak perlu input `paid_amount`

**d. UI edit modal — bagian Payment Method** (tidak ada — edit modal tidak mengubah payment method)

### 4. Frontend — Member Orders Index (`resources/js/pages/member/orders/Index.vue`)

**a. `paymentMethods`**
- Tambah `{ value: 'deposit', label: 'Deposit', icon: '...' }`

**b. Props**
- Terima `depositBalance: number` dari backend

**c. UI**
- Jika `paymentMethod === 'deposit'`:
  - Tampilkan saldo deposit member saat ini
  - Validasi saldo cukup vs total keranjang
  - Tampilkan pesan jika saldo tidak cukup

### 5. Frontend — Label di semua halaman

**a. `resources/js/pages/admin/orders/Receipt.vue`**
- Tambah `deposit: 'Deposit'` ke `paymentLabels`

**b. `resources/js/pages/member/orders/History.vue`**
- Tambah `deposit: 'Deposit'` ke `paymentLabels`

---

## Asumsi & Keputusan

- **Deposit = lunas.** Saat pilih deposit, order otomatis dianggap lunas (`paid_amount = total_amount`, `change = 0`). Tidak ada partial deposit.
- **Tidak bisa refund otomatis.** Jika order dibatalkan setelah bayar deposit, refund saldo dilakukan manual oleh admin (di luar scope task ini).
- **Edit order tidak mengubah payment_method.** Edit modal hanya ubah status & items. Payment method sudah fix saat order dibuat.
- **Admin lihat saldo member via dropdown.** Saat admin pilih member di create modal, saldo deposit member itu ditampilkan jika metode deposit dipilih.

---

## Verifikasi

1. Buka `admin/orders`, buat order baru pilih metode Deposit — pastikan saldo terbaca & terpotong
2. Coba dengan saldo tidak cukup — harus muncul error validasi
3. Cek receipt — label "Deposit" muncul
4. Cek history member — label "Deposit" muncul
5. Cek tabel `deposit_transactions` — ada record `type = payment` untuk order tsb
6. Jalankan `php artisan test` untuk pastikan tidak ada regression
