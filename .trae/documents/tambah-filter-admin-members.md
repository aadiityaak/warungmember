# Plan: Tambah Filter Halaman Admin Members

## Ringkasan
Menambahkan 3 filter ke halaman `/admin/members`:
1. Filter by Outlet (dropdown)
2. Filter by Tanggal Daftar (date range)
3. Filter by Status Aktif (aktif/nonaktif/semua)

---

## Current State

### Backend (`MemberController@index`)
- Menerima query params: `search`, `sort`, `direction`
- Query: `User::where('role', 'member')` dengan conditional search + sort
- Return: `members` (paginated) + `filters` (search/sort/direction)

### Frontend (`resources/js/pages/admin/members/Index.vue`)
- Props: `members` (paginated User[]) + `filters: { search?, sort?, direction? }`
- Search form pakai `useForm`, GET request dengan `preserveState`
- Sort via `sortUrl()` helper, preserve search param
- Pagination preserve `search`, `sort`, `direction`

### Data Model
- `users` table: `id`, `name`, `email`, `role`, `created_at`, `deleted_at` (soft delete)
- `members` table: `id`, `user_id`, `member_code`, `total_points`, `deposit_balance`, `last_outlet_id`, `birth_date`
- `outlets` table: `id`, `name`, `is_active`

---

## Perubahan

### 1. Backend: `MemberController@index`

**A. Load data outlet untuk dropdown**
```php
use App\Models\Outlet;

// di index(), sebelum return:
$outlets = Outlet::where('is_active', true)->orderBy('name')->get(['id', 'name']);
```

**B. Tambah conditional query untuk 3 filter baru**
```php
// Filter by status (soft delete)
->when($request->status === 'active', fn ($q) => $q->whereNull('deleted_at'))
->when($request->status === 'inactive', fn ($q) => $q->whereNotNull('deleted_at'))

// Filter by outlet
->when($request->outlet_id, fn ($q, $outletId) => $q->whereHas('member', fn ($q) => $q->where('last_outlet_id', $outletId)))

// Filter by date range
->when($request->date_from, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
->when($request->date_to, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
```

**C. Update return value**
```php
return inertia('admin/members/Index', [
    'members' => $members,
    'filters' => $request->only('search', 'sort', 'direction', 'outlet_id', 'date_from', 'date_to', 'status'),
    'outlets' => $outlets,
]);
```

### 2. Frontend: `resources/js/pages/admin/members/Index.vue`

**A. Update props type**
```ts
filters: { search?: string; sort?: string; direction?: string; outlet_id?: string; date_from?: string; date_to?: string; status?: string };
outlets: { id: number; name: string }[];
```

**B. Inisialisasi form fields**
```ts
const form = useForm({
    search: filters.search ?? '',
    outlet_id: filters.outlet_id ?? '',
    date_from: filters.date_from ?? '',
    date_to: filters.date_to ?? '',
    status: filters.status ?? '',
});
```

**C. Tambah 3 filter control ke template** (di antara search form dan tombol Tambah)
```html
<!-- Baris filter: outlet, status, date range -->
<div class="mb-6 flex flex-wrap items-center gap-3">
    <!-- Search (existing) -->
    <form @submit.prevent="submit" class="flex-1 min-w-[200px]">
        <input v-model="form.search" ... />
    </form>

    <!-- Outlet filter -->
    <select v-model="form.outlet_id" @change="submit" class="rounded-full border-0 bg-[#f6f6f3] px-4 py-3 text-sm ...">
        <option value="">Semua Outlet</option>
        <option v-for="outlet in outlets" :key="outlet.id" :value="outlet.id">{{ outlet.name }}</option>
    </select>

    <!-- Status filter -->
    <select v-model="form.status" @change="submit" class="rounded-full border-0 bg-[#f6f6f3] px-4 py-3 text-sm ...">
        <option value="">Semua Status</option>
        <option value="active">Aktif</option>
        <option value="inactive">Nonaktif</option>
    </select>

    <!-- Date range -->
    <input type="date" v-model="form.date_from" @change="submit" class="rounded-full border-0 bg-[#f6f6f3] px-4 py-3 text-sm ..." />
    <input type="date" v-model="form.date_to" @change="submit" class="rounded-full border-0 bg-[#f6f6f3] px-4 py-3 text-sm ..." />

    <Button as="child">
        <Link :href="route('admin.members.create')">+ Tambah</Link>
    </Button>
</div>
```

**D. Update `sortUrl()` dan pagination link** untuk preserve semua filter params
```ts
function sortUrl(column: string): string {
    const params: Record<string, string | number> = {};
    if (form.search) params.search = form.search;
    if (form.outlet_id) params.outlet_id = form.outlet_id;
    if (form.date_from) params.date_from = form.date_from;
    if (form.date_to) params.date_to = form.date_to;
    if (form.status) params.status = form.status;
    params.sort = column;
    params.direction = currentSort === column && currentDirection === 'asc' ? 'desc' : 'asc';
    return route('admin.members.index', params);
}
```

Sama untuk pagination link — tambah semua filter params.

**E. Auto-submit on change** — tiap dropdown/date field pake `@change="submit"` biar langsung apply filter tanpa klik tombol.

---

## Asumsi & Keputusan

| Asumsi | Keputusan |
|--------|-----------|
| User ingin filter langsung apply tanpa tombol "Cari" terpisah | Tiap perubahan dropdown/date langsung submit form (GET) |
| Outlet yang tampil hanya yang aktif | Query `where('is_active', true)` |
| Status "Nonaktif" = user di-soft-delete | Filter berdasarkan `deleted_at IS NOT NULL` |
| Gak perlu pagination reset manual | `withQueryString()` dan `preserveState` handle otomatis |
| Format tanggal pakai date input native | Simple, no need date picker library |

---

## Verification

1. Akses `/admin/members` — filter dropdown dan date input muncul
2. Pilih outlet — list member terfilter sesuai outlet terakhir
3. Pilih status "Nonaktif" — member yang dihapus (soft delete) muncul
4. Isi date range — member dalam range tanggal daftar muncul
5. Kombinasi filter — semua filter bekerja bersamaan
6. Sort & pagination — tetap berfungsi dengan filter aktif
7. Filter ikut tersimpan di URL (bisa di-bookmark/share)
