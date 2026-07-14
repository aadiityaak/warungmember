# Plan: Tambah Tombol Toggle Grid/List View di Halaman Member Products

## Ringkasan
Menambahkan tombol toggle untuk mengganti mode tampilan produk antara grid (card, seperti sekarang) dan list (horizontal, image kiri-info kanan) di halaman `/member/products`.

---

## Current State

### Halaman `/member/products`
- **Route:** `member.products.index` (GET)
- **Controller:** `Member\ProductController@index` — return `products` (all active, latest) + `categories`
- **Vue:** `resources/js/pages/member/products/Index.vue`
- **Tampilan:** Produk dalam `grid grid-cols-2 gap-3`, masing-masing card vertikal (image square di atas, info di bawah, tombol cart di bawah info)
- **State:** `selectedCategory` (ref), `searchQuery` (ref), `filteredProducts` (computed)
- **Tidak ada** pattern toggle grid/list di codebase

---

## Perubahan

Hanya 1 file yang diubah: `resources/js/pages/member/products/Index.vue`

### 1. Tambah state `viewMode`
```ts
const viewMode = ref<'grid' | 'list'>('grid');
```
Gunakan `localStorage` biar preferensi tersimpan:
```ts
const viewMode = ref<'grid' | 'list'>((localStorage.getItem('productViewMode') as 'grid' | 'list') || 'grid');
function toggleView(mode: 'grid' | 'list') {
    viewMode.value = mode;
    localStorage.setItem('productViewMode', mode);
}
```

### 2. Tambah tombol toggle di header area (sebelah kanan search atau di atas grid)
Dua icon button (grid icon + list icon), active state pakai bg-\[#E22625\]/text-white.

### 3. Template — conditional grid/list

**Grid mode** (existing):
```html
<div v-if="viewMode === 'grid'" class="grid grid-cols-2 gap-3">
    <!-- card existing -->
</div>
```

**List mode** (baru):
```html
<div v-else class="flex flex-col gap-2">
    <div v-for="product in filteredProducts" :key="product.id"
         class="flex gap-3 overflow-hidden rounded-2xl border border-[#dadad3] bg-white p-3 transition-shadow hover:shadow-md">
        <!-- Image (smaller, aspect-square 80x80) -->
        <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-[#f6f6f3]">
            <img ... class="h-full w-full object-cover" />
            <div v-if="product.is_on_discount" class="absolute left-1 top-1 rounded-full bg-[#E22625] px-1.5 py-0.5 text-[8px] font-bold text-white">DISKON</div>
        </div>
        <!-- Info -->
        <div class="flex min-w-0 flex-1 flex-col justify-center">
            <h3 class="text-sm font-semibold leading-[1.3] text-[#000000] line-clamp-1">{{ product.name }}</h3>
            <p class="text-xs leading-[1.4] text-[#91918c]">+{{ product.points_earned }} poin</p>
            <div class="flex items-center gap-1.5">
                <span class="text-sm font-bold text-[#E22625]">Rp{{ product.current_price.toLocaleString('id-ID') }}</span>
                <span v-if="product.is_on_discount" class="text-xs text-[#91918c] line-through">Rp{{ product.price.toLocaleString('id-ID') }}</span>
            </div>
        </div>
        <!-- Add to Cart button -->
        <button @click="addToCart(product)"
            class="shrink-0 self-center rounded-full px-4 py-1.5 text-xs font-bold transition-colors"
            :class="isInCart(product.id) ? 'bg-[#E22625]/10 text-[#E22625]' : 'bg-[#f6f6f3] text-[#000000] hover:bg-[#E22625] hover:text-white'">
            <svg v-if="isInCart(product.id)" class="inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            {{ isInCart(product.id) ? 'Di Keranjang' : '+ Keranjang' }}
        </button>
    </div>
</div>
```

### 4. Tata letak tombol toggle
Letakkan di baris yang sama dengan search input atau di antara search dan category chips. Paling praktis: di kanan search input, dibungkus `flex items-center gap-2`.

---

## Asumsi & Keputusan

| Asumsi | Keputusan |
|--------|-----------|
| Preferensi tampilan perlu diingat | Pakai `localStorage` |
| List view tetap pakai card (horizontal) | Bukan table, tetap pakai border rounded-2xl biar konsisten |
| Tidak perlu backend change | Semua client-side, cukup toggle CSS layout |
| Icon toggle cukup sederhana | Pakai SVG inline (grid 4 kotak / list 3 garis) |

---

## Verification

1. Buka `/member/products` — default tampil grid (seperti sekarang)
2. Klik icon list — produk berubah jadi list horizontal
3. Klik icon grid — kembali ke grid
4. Refresh halaman — preferensi masih tersimpan
5. Filter/search kategori — tetap berfungsi di kedua mode
