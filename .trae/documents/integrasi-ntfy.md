# Plan Integrasi ntfy — Push Notification Sederhana & Self-Hosted

## Ringkasan

Ganti seluruh infrastruktur push notification (FCM + Web Push VAPID + minishlink/web-push + @capacitor/push-notifications) dengan **ntfy** — satu protokol HTTP pub/sub sederhana yang bisa di-selfhost. Tidak perlu Google Services, tidak perlu VAPID, tidak perlu service worker untuk push.

---

## Current State

| Komponen | Status | Masalah |
|----------|--------|---------|
| FCM (Android) | `google-services.json` tidak ada | **Tidak berfungsi** — Legacy HTTP API akan di-deprecate |
| Web Push (VAPID) | `VAPID_*` keys kosong di `.env.example` | Tidak aktif, library `minishlink/web-push` terinstall tapi tidak dipakai otomatis (hanya dari BroadcastController) |
| Database Notification | Berfungsi penuh | Tetap dipertahankan sebagai history in-app |
| `SendPushNotification` Job | Hanya dispatch dari Broadcast | OrderController hanya simpan DB notif, tidak kirim push |

**Kesimpulan:** Push notification saat ini secara efektif **tidak jalan**. Daripada memperbaiki FCM + Web Push secara terpisah, kita ganti total dengan ntfy yang lebih sederhana.

---

## Arsitektur Target

```
┌─────────────────────┐     HTTP POST       ┌──────────────┐     SSE/WS        ┌──────────────────┐
│  Laravel Backend    │ ──────────────────>  │  ntfy Server │ ───────────────>  │  Client (Web/App)│
│  (app/Jobs/Ntfy...) │    POST /{topic}     │ (self-host)  │    EventSource    │  resources/js/   │
│                     │                      │              │                   │  useNtfy.ts      │
└─────────────────────┘                      └──────────────┘                   └──────────────────┘
      │                                                    ▲
      │                                                    │
      v                                                    │
  Database Notifications                                   │
  (in-app history)                                         │
                                                    Setiap user subscribe
                                                    ke topic pribadi:
                                                    wm-{userId}-{token}
```

### Alur:

1. **User login** → frontend subscribe ke topic ntfy via EventSource (SSE)
2. **Server men-trigger notif** → dispatch job → HTTP POST ke ntfy server
3. **ntfy server** → push ke semua subscriber topic via SSE
4. **Client** terima event → tampilkan browser notification + update UI

### Kenapa EventSource (SSE) bukan WebSocket?

- Support native di semua browser & WebView (termasuk Capacitor)
- Tidak perlu library tambahan — `new EventSource(url)` bawaan JS
- Satu arah (server→client) pas untuk notifikasi
- Auto-reconnect built-in

---

## Perubahan File

### A. Setup ntfy Server

| File | Action |
|------|--------|
| `docker-compose.yml` (baru, root project) | Tambah service ntfy untuk deployment Docker |
| `.env.example` | Tambah `NTFY_SERVER_URL`, `NTFY_TOPIC_SECRET` |
| `config/services.php` | Tambah `ntfy` config entry |

**ntfy server (Docker):**
```yaml
services:
  ntfy:
    image: binwiederhier/ntfy
    container_name: ntfy
    ports:
      - "2586:80"
    volumes:
      - ntfy_data:/var/lib/ntfy
    environment:
      - TZ=Asia/Jakarta
      - NTFY_BASE_URL=https://ntfy.example.com
      - NTFY_AUTH_DEFAULT_ACCESS=deny-all
      - NTFY_AUTH_FILE=/var/lib/ntfy/auth.db
    restart: unless-stopped
```

### B. Backend — Kirim Notifikasi

| File | Action |
|------|--------|
| `app/Jobs/SendPushNotification.php` | **Tulis ulang** jadi ntfy HTTP POST |
| `app/Http/Controllers/Admin/BroadcastController.php` | Tidak perlu diubah (dispatch job yang sama) |
| `app/Http/Controllers/Member/OrderController.php` | Tambah dispatch `SendPushNotification` setelah simpan notif |
| `composer.json` | Hapus `minishlink/web-push` |

**SendPushNotification.php baru:**
```php
class SendPushNotification implements ShouldQueue
{
    public function __construct(
        public Member $member,
        public array $payload   // title, body, data, type
    ) {}

    public function handle(): void
    {
        $topic = $this->member->ntfy_topic;
        $token = $this->member->ntfy_token;

        Http::post(config('services.ntfy.server').'/'.$topic, [
            'title' => $payload['title'],
            'message' => $payload['body'],
            'tags' => $payload['type'] ?? 'info',
            'priority' => 4,
            'click' => $payload['data']['url'] ?? null,
            'icon' => config('app.url').'/img/icon-192.png',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);
    }
}
```

### C. Backend — Subscription/Topic Management

| File | Action |
|------|--------|
| `app/Http/Controllers/Member/PushSubscriptionController.php` | **Tulis ulang** jadi ntfy topic management |
| `database/migrations/xxxx_update_push_subscriptions_for_ntfy.php` (baru) | Ubah schema `push_subscriptions` |
| `app/Models/PushSubscription.php` | Update fillable + casts |
| `routes/web.php` | Update route handler |

**Schema baru `push_subscriptions` (migration):**
```php
// Rename/hapus kolom: endpoint, auth, p256dh, fcm_token
// Tambah:
$table->string('ntfy_topic')->unique()->after('member_id');
$table->string('ntfy_token')->after('ntfy_topic');
$table->boolean('subscribed')->default(true)->after('ntfy_token');
```

**PushSubscriptionController endpoints baru:**
- `POST member/push/subscribe` → generate topic name + ntfy access token, simpan ke DB, return topic + token ke client
- `POST member/push/unsubscribe` → set `subscribed = false`
- `GET member/push/status` → return subscribed status + topic

### D. Frontend — Subscribe & Receive

| File | Action |
|------|--------|
| `resources/js/composables/usePushNotification.ts` | **Tulis ulang** jadi `useNtfy.ts` — EventSource ke ntfy |
| `resources/sw.ts` | Hapus event `push` & `notificationclick` handler — pindah ke main thread |
| `public/dev-sw.js` | Hapus event `push` & `notificationclick` handler |
| `vite.config.ts` | Hapus `injectManifest` strategy atau biarkan service worker hanya untuk cache |

**useNtfy.ts logic:**
```ts
// 1. Dapatkan topic + token dari server (POST /member/push/subscribe)
// 2. Buat EventSource ke `{ntfyServer}/{topic}/sse`
// 3. On message: parse JSON, tampilkan Notification API
// 4. Auto-reconnect on error
```

### E. Hapus (Cleanup)

| File | Alasan |
|------|--------|
| `config/webpush.php` | Tidak dipakai lagi |
| `app/` — tidak ada file khusus FCM/VAPID lain | Sudah di-cover |
| `minishlink/web-push` via composer | Hapus dependency |
| `@capacitor/push-notifications` via npm | Hapus dependency |

---

## Daftar Lengkap File Berubah

### File Baru
1. `docker-compose.yml` — ntfy service (opsional, untuk deployment Docker)
2. `database/migrations/xxxx_update_push_subscriptions_for_ntfy.php`

### File Diubah
3. `resources/js/composables/useNtfy.ts` — ganti `usePushNotification.ts`
4. `app/Jobs/SendPushNotification.php` — tulis ulang ke ntfy HTTP POST
5. `app/Http/Controllers/Member/PushSubscriptionController.php` — tulis ulang
6. `app/Models/PushSubscription.php` — update fillable/casts
7. `config/services.php` — tambah config ntfy
8. `.env.example` — tambah env ntfy
9. `app/Http/Controllers/Member/OrderController.php` — dispatch push job setelah simpan notif
10. `app/Http/Controllers/Admin/OrderController.php` — dispatch push job setelah update status
11. `routes/web.php` — update route signature

### File Dihapus
12. `resources/js/composables/usePushNotification.ts` — diganti useNtfy.ts
13. `config/webpush.php` — tidak dipakai
14. `composer.json` → hapus `minishlink/web-push` (manual edit)

### Service Worker (dibersihkan)
15. `resources/sw.ts` — hapus push event handlers
16. `public/dev-sw.js` — hapus push event handlers

### npm (hapus dependency)
17. `package.json` → hapus `@capacitor/push-notifications`

---

## Pertimbangan & Asumsi

### Naming Topic
- Format: `wm-{userId}-{8charRandomHex}`
- Contoh: `wm-42-a3f8c91b`
- Random suffix mencegah orang nebak topic user lain
- Token disimpan di tabel `push_subscriptions`

### Keamanan
- ntfy server di VPS dengan firewall — hanya allow dari IP server Laravel + publik
- Topic token (access token) digunakan di URL SSE — via HTTPS, token aman
- Authorisasi ntfy: setiap topic punya access token read-only untuk client, write-only untuk server
- Bisa pakai ntfy built-in auth (`auth.db`) untuk management

### Dependency Baru
- **Server:** Docker image `binwiederhier/ntfy`
- **Backend:** Tidak ada — cukup `Illuminate\Support\Facades\Http` (built-in Laravel)
- **Frontend:** Tidak ada — `EventSource` native browser API

### Keuntungan dibanding Sekarang
- Satu protokol untuk web + mobile
- Tidak perlu Google Play Services (FCM)
- Tidak perlu VAPID key setup
- Tidak perlu service worker untuk push (opsional bisa tetap dipakai)
- Self-hosted — full kontrol data
- Latensi rendah (langsung ke server sendiri)

### Kekurangan / Resiko
- **Server tambahan:** ntfy perlu di-run sebagai service terpisah (Docker/binary)
- **Android di background:** SSE di WebView akan mati saat app di background — tidak bisa push notif saat app tertutup. Solusi: ntfy punya Android app native sendiri (`ntfy Android app`), atau perlu FCM sebagai fallback untuk background push. Alternatif lain: pwa/notification trigger tetap pake ServiceWorker + push api dari browser.

Wait — ini penting. SSE di WebView Capacitor **tidak akan berfungsi saat app di background**. Ini adalah keterbatasan fundamental WebView di Android.

### Solusi Background Push

**Opsi A: FCM tetap untuk background + ntfy untuk foreground (Hybrid)**
- Foreground: ntfy EventSource (real-time, zero latency)
- Background: FCM (via Capacitor plugin) untuk bangunkan app / tampilkan notif di system tray
- Kompleksitas: sedang, tetap perlu FCM server key + google-services.json

**Opsi B: ntfy Android app sebagai notifier**
- User install ntfy app, subscribe ke topic masing-masing
- Tidak perlu Capacitor push plugin sama sekali
- Kekurangan: user perlu 2 app

**Opsi C: Web Push tetap untuk background + ntfy untuk foreground (Hybrid)**
- Web Push (VAPID + Service Worker) menangani notif saat app tertutup
- ntfy menangani notif real-time saat app terbuka
- Kompleksitas: rendah, service worker tetap dipertahankan

**Opsi D: Service Worker + Push API tanpa Web Push library (minimal VAPID)**
- ntfy semua notifikasi real-time
- Untuk background: gunakan Web Push API browser langsung (pushManager.subscribe) + service worker
- Pertahankan `minishlink/web-push` hanya untuk background push

**Rekomendasi: Opsi C** — ntfy untuk foreground (SSE, real-time) + Web Push tetap untuk background notif saat app tertutup. Ini paling praktis:
- Tidak perlu FCM (Google Services)
- Tidak perlu google-services.json
- Web Push + ntfy berjalan paralel
- ntfy jadi push channel utama, Web Push sebagai fallback background
- Saat app di foreground: notif langsung muncul via ntfy SSE (tanpa service worker)
- Saat app di background: service worker push event menangani

---

## Tahapan Implementasi

### Fase 1 — Setup ntfy Server
1. Setup Docker container ntfy di VPS
2. Konfigurasi basic auth / access token
3. Test publish & subscribe via curl

### Fase 2 — Backend: Kirim via ntfy
1. Tambah config ntfy di `config/services.php`
2. Tulis ulang `SendPushNotification.php` → HTTP POST ke ntfy
3. Update controller: `OrderController` dispatch push job
4. Hapus dependency `minishlink/web-push`

### Fase 3 — Frontend: Subscribe & Receive
1. Buat `useNtfy.ts` — EventSource ke ntfy server
2. Ganti `PushSubscriptionController` → ntfy topic management
3. Migration: tambah kolom `ntfy_topic` + `ntfy_token` ke `push_subscriptions`

### Fase 4 — Hybrid Background Push (Web Push tetap jalan)
1. Service worker tetap dipertahankan untuk background push
2. `SendPushNotification` dispatch 2 job: ntfy (foreground) + web push (background)
3. Atau: satu job kirim ke ntfy + ke web push subscription

### Fase 5 — Cleanup
1. Hapus `config/webpush.php` (opsional, jika Web Push dihapus total)
2. Hapus `@capacitor/push-notifications` dari npm
3. Hapus `usePushNotification.ts`
4. Hapus FCM code dari `SendPushNotification` (sendFcm method)

---

## Verification

1. `php artisan test --compact --filter=Notification` — semua test notification pass
2. Docker container ntfy running: `docker ps | grep ntfy`
3. `curl -d '{"title":"test","message":"hello"}' {ntfy-url}/wm-test-123` — notif masuk di client
4. Web: open app → notif real-time muncul tanpa refresh
5. Android (Capacitor): app foreground → notif real-time via SSE
6. Android background: service worker push notification masih muncul (Opsi C)

---

## Prioritas vs Existing Android Native Plan

Item di plan ini kompatibel dengan plan `optimasi-android-native.md`:
- **Tidak bertentangan** — hanya menyentuh layer push notification
- **Tidak menghilangkan** P1-P3 items di plan Android native
- **Mengganti** implementasi push notification yang saat ini tidak berfungsi
- **Perlu diselaraskan** — setelah integrasi ntfy, item "Service worker & push notification masih jalan" di verification perlu disesuaikan
