# Plan: Improve Visitor Landing Page (Welcome Page)

## Current State Analysis

Halaman `/` (Welcome.vue) saat ini:
- Route: `Route::inertia('/', 'Welcome')` — no server props passed
- Layout: Memakai `MemberLayout` — padahal ini layout untuk member login (ada bottom nav 5 menu, cart icon, semuanya指向 `member.*` routes)
- Content: Header sapaan, banner carousel, CTA daftar/login, 4 card fitur statis
- **Masalah utama**: Layout member (bottom nav, cart icon) tidak relevan untuk visitor — semua link di bottom nav akan redirect ke login
- "Cari Outlet" button → login, bukan listing outlet
- Halaman fully static, tidak ada dynamic content dari server

---

## Proposed Changes

### 1. Buat VisitorLayout (layout khusus visitor)

**File baru:** `resources/js/layouts/VisitorLayout.vue`

**Apa:**
- Layout minimalis tanpa bottom nav
- Header simpel: logo + 2 tombol (Masuk, Daftar)
- Main content area (max-w-md, centered)
- Footer minimal: copyright, link terms/privacy

**Kenapa:**
- Memisahkan experience visitor dari member
- Bottom nav member bikin bingung visitor (link ke halaman yang butuh auth)
- Header dengan CTA login/register langsung mendorong konversi

---

### 2. Enhance Welcome.vue dengan konten dinamis & section baru

**File diubah:** `resources/js/pages/Welcome.vue`

**Apa yang diubah:**

#### a. Server Props — kirim data dari route
Ubah route dari `Route::inertia('/', 'Welcome')` jadi controller atau closure dengan props:

```php
Route::get('/', function () {
    // Ambil produk featured (active, dengan gambar)
    $featuredProducts = Product::where('is_active', true)
        ->whereNotNull('image')
        ->take(4)
        ->get()
        ->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'price' => $p->price,
            'current_price' => $p->current_price,
            'image' => $p->image,
            'is_on_discount' => $p->is_on_discount,
        ]);

    return Inertia::render('Welcome', [
        'featuredProducts' => $featuredProducts,
        'stats' => [
            'total_members' => Member::count(),
            'total_outlets' => Outlet::count(),
        ],
    ]);
})->name('home');
```

#### b. Section baru di Welcome.vue

**1. Hero Section (enhance)**
- Tambah logo brand di atas "Halo, Selamat Datang!"
- Subtitle lebih jelas tentang value proposition
- "Cari Outlet" → ganti jadi link ke halaman outlet publik (atau listing outlet), bukan login

**2. Statistik / Social Proof**
- Total member terdaftar
- Total outlet
- Total poin sudah dibagikan (nullable — bisa dari cache)
- Card statistik dengan icon, gaya grid 2 kolom

**3. Featured Products (produk unggulan)**
- Preview 4 produk (ambil dari DB, kirim via props)
- Card produk dengan gambar, nama, harga, badge diskon
- Kalau visitor tertarik, bisa lihat produk → dorong login/register
- 2 kolom grid, mirip member dashboard tapi tanpa tombol add-to-cart
- Tombol "Lihat Menu Lengkap" → link ke `login` (atau halaman menu publik jika ada)

**4. Testimoni / Review (statis dulu)**
- Section quotes dari "member" tentang benefit jadi member
- Bisa dikelola dari admin nantinya, untuk sekarang pakai data statis di component
- Card style dengan avatar placeholder, nama, rating bintang, dan quote pendek

**5. Cara Kerja (How It Works)**
- 3 langkah simpel: Daftar → Belanja → Kumpulkan Poin & Tukar Reward
- Visual step dengan icon + nomor urut
- Gaya horizontal timeline di mobile

**6. Enhanced CTA Section**
- Background tetap gradient merah
- Judul lebih kuat: "Siap Jadi Member Warung Mas Mbull?"
- Tambah benefit bullet points (3-4 poin)
- Tombol "Daftar Gratis" lebih prominent

**7. PWA Install Banner**
- Detect if app is installable (`beforeinstallprompt` event)
- Tampilkan banner "Install Aplikasi" dengan tombol install
- Bisa dismiss

**8. Footer**
- Copyright
- Link Terms & Conditions, Privacy Policy
- Social media (WhatsApp dari branding)

---

### 3. Update app.ts layout resolver

**File diubah:** `resources/js/app.ts`

Tambah case untuk Welcome page biar pakai VisitorLayout, bukan fallback ke layout resolver yang return null.

Sebenernya karena Welcome.vue define layout secara eksplisit via `defineOptions`, cukup ganti `defineOptions({ layout: MemberLayout })` jadi `defineOptions({ layout: VisitorLayout })` di Welcome.vue.

---

### 4. Buat BannerCarousel sebagai reusable component

**File baru:** `resources/js/components/BannerCarousel.vue`

Ekstrak logika carousel dari Welcome.vue jadi component reusable. Bisa dipake di Welcome dan member dashboard.

---

## Files Affected

| File | Action |
|------|--------|
| `resources/js/layouts/VisitorLayout.vue` | **CREATE** — layout simpel visitor |
| `resources/js/pages/Welcome.vue` | **EDIT** — tambah sections, ganti layout |
| `routes/web.php` | **EDIT** — route `/` jadi closure dgn props |
| `resources/js/components/BannerCarousel.vue` | **CREATE** — reusable carousel |
| `resources/js/app.ts` | **EDIT** — update layout resolver (mungkin tidak perlu) |

---

## Prioritas Implementasi

**High priority (core improvement):**
1. VisitorLayout — layout terpisah untuk visitor
2. Welcome.vue — ganti layout, enhance hero, CTA, featured products
3. Route — kirim props produk & statistik

**Medium priority:**
4. Statistik/social proof section
5. How it works section
6. BannerCarousel reusable component

**Low priority (nice to have):**
7. Testimoni section
8. PWA install banner
9. Footer

---

## Verification

1. Buka `/` tanpa login → lihat layout baru tanpa bottom nav
2. Header cuma ada logo + tombol Masuk/Daftar
3. Produk featured muncul dari DB
4. Statistik member/outlet muncul
5. Semua link berfungsi (login, register, outlet)
6. Tidak ada link ke member routes yang broken
7. `php artisan test` — pastikan existing tests masih pass
8. `vendor/bin/pint --format agent` — format PHP code
