# Rencana: Push Notification untuk Broadcast

## Ringkasan

Menambahkan Web Push API ke sistem notifikasi yang sudah ada. Saat admin mengirim broadcast notifikasi, browser member akan menerima push notification secara real-time — tidak hanya saat membuka halaman notifikasi.

---

## Arsitektur Saat Ini

- **Broadcast**: Admin buat → insert massal ke tabel `notifications` (DB only)
- **Notifikasi**: Member lihat di halaman `/member/notifications` — hanya muncul saat halaman di-request
- **PWA**: Service worker auto-generated (generateSW) — hanya precache + runtime caching API, tanpa custom listener
- **Push Infrastructure**: Tidak ada — tidak ada tabel subscription, VAPID, atau event handler SW

---

## Pendekatan

Gunakan **Web Push API** standar:
1. PHP library `minishlink/web-push` untuk mengirim push dari backend
2. Service worker custom (mode `injectManifest`) dengan event `push` + `notificationclick`
3. Frontend minta izin + subscribe Push API
4. Simpan subscription ke tabel `push_subscriptions`

---

## Perubahan yang Diperlukan

### 1. Install library + generate VAPID keys

- `composer require minishlink/web-push`
- Generate VAPID keys via CLI: `php artisan vendor:publish --tag=webpush-config` atau manual menggunakan `bin/web-push generate-keys`
- Simpan `VAPID_PUBLIC_KEY` + `VAPID_PRIVATE_KEY` di `.env`
- Tambah config `config/webpush.php` dengan subject (mailto:email)

### 2. Migration: tabel `push_subscriptions`

- `id`, `member_id` (FK → members), `endpoint` (text), `auth` (string), `p256dh` (string), `user_agent` (string, nullable), `created_at`
- `$casts`: endpoint akan diencrypt? Tidak perlu, simpan plaintext
- Unique constraint on (member_id, endpoint) — handle multiple devices/browser

### 3. Model: `App\Models\PushSubscription`

- `$fillable`: member_id, endpoint, auth, p256dh, user_agent
- `$hidden`: (tidak ada yang sensitif pada model ini)
- Relasi `belongsTo(Member::class)`

### 4. Controller: `App\Http\Controllers\Member\PushSubscriptionController`

Methods:

| Method | Route | Fungsi |
|---|---|---|
| `subscribe()` | POST `push/subscribe` | Simpan/update subscription |
| `unsubscribe()` | POST `push/unsubscribe` | Hapus subscription berdasarkan endpoint |
| `vapidKey()` | GET `push/vapid-key` | Return public key (biar bisa di-cache di client tanpa hardcode) |

### 5. Route (web.php)

Di grup `member` (middleware auth, verified, role:member):
```
GET  push/vapid-key     →  [PushSubscriptionController, 'vapidKey']     →  member.push.vapid-key
POST push/subscribe     →  [PushSubscriptionController, 'subscribe']    →  member.push.subscribe
POST push/unsubscribe   →  [PushSubscriptionController, 'unsubscribe']  →  member.push.unsubscribe
```

### 6. Vite config: switch dari generateSW → injectManifest

Ubah `vite.config.ts` PWA plugin:
- `strategies: 'injectManifest'`
- `srcDir: 'resources'` (biar SW source di `resources/sw.js`)
- `filename: 'sw.ts'` (source file, akan di-compile Vite)
- Hapus konfigurasi `workbox` yang spesifik ke generateSW; pindahkan runtime caching ke file `sw.ts`

### 7. Custom Service Worker: `resources/sw.ts`

```ts
/// <reference lib="webworker" />
import { clientsClaim } from 'workbox-core';
import { precacheAndRoute } from 'workbox-precaching';
import { registerRoute } from 'workbox-routing';
import { NetworkFirst } from 'workbox-strategies';

declare const self: ServiceWorkerGlobalScope;

clientsClaim();
precacheAndRoute(self.__WB_MANIFEST);

// Runtime caching untuk API calls — sama seperti konfigurasi generateSW sebelumnya
registerRoute(
  ({ url }) => url.pathname.startsWith('/api/'),
  new NetworkFirst({ cacheName: 'api-cache', networkTimeoutSeconds: 5 })
);

// Event: push — diterima saat server kirim push notification
self.addEventListener('push', (event) => {
  let data: { title: string; body: string; icon?: string; badge?: string; url?: string } = {
    title: 'WarungMember',
    body: '',
  };
  if (event.data) {
    try { data = { ...data, ...event.data.json() }; } catch { /* ignore */ }
  }
  const options: NotificationOptions = {
    body: data.body,
    icon: data.icon || '/pwa-icons/pwa-192x192.png',
    badge: data.badge || '/pwa-icons/pwa-192x192.png',
    data: { url: data.url || '/member/notifications' },
  };
  event.waitUntil(self.registration.showNotification(data.title, options));
});

// Event: notificationclick — saat user klik notifikasi
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const url = event.notification.data?.url || '/member/notifications';
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
      for (const client of clients) {
        if (client.url.includes(self.location.origin) && 'focus' in client) {
          client.navigate(url);
          return client.focus();
        }
      }
      return self.clients.openWindow(url);
    })
  );
});
```

### 8. Frontend: Push Subscription Manager

Buat composable `resources/js/composables/usePushNotification.ts`:

```ts
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function usePushNotification() {
  const subscribed = ref(false);
  const supported = 'serviceWorker' in navigator && 'PushManager' in window;

  async function subscribe() {
    if (!supported) return;
    try {
      const registration = await navigator.serviceWorker.ready;
      let subscription = await registration.pushManager.getSubscription();
      if (!subscription) {
        const res = await fetch(route('member.push.vapid-key'));
        const { publicKey } = await res.json();
        subscription = await registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: publicKey,
        });
      }
      // Kirim ke server
      await fetch(route('member.push.subscribe'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '...' },
        body: JSON.stringify(subscription.toJSON()),
      });
      subscribed.value = true;
    } catch { /* user denied / error */ }
  }

  // Panggil subscribe() otomatis jika user sudah grant permission
  async function init() {
    if (!supported) return;
    const permission = await Notification.requestPermission();
    if (permission === 'granted') {
      await subscribe();
    }
  }

  return { supported, subscribed, init, subscribe };
}
```

### 9. Integrasi di MemberLayout

Di `MemberLayout.vue`, panggil `usePushNotification().init()` di `onMounted`:
- Cek `Notification.permission`
- Jika `'granted'` → subscribe + kirim ke server
- Jika `'default'` → request permission, lalu subscribe
- Jika `'denied'` → skip
- Jalankan sekali, tidak perlu reactive UI

### 10. BroadcastController: Kirim Push

Setelah insert notifikasi ke database (di method `store()`), tambahkan langkah:
1. Query member yang di-target + punya push subscriptions
2. Untuk setiap subscription, dispatch `SendPushNotification` job (atau kirim synchronous jika member sedikit)
3. Payload: `{ title, body, icon: '/pwa-icons/pwa-192x192.png', url: '/member/notifications' }`

Buat Job `App\Jobs\SendPushNotification`:
```
- handle(): menerima PushSubscription model + payload array
- Menggunakan minishlink/web-push library untuk mengirim
- Catch dan hapus subscription jika endpoint expired (410 Gone)
```

### 11. Controller: Push Subscription

```php
class PushSubscriptionController extends Controller
{
    public function vapidKey(): JsonResponse
    {
        return response()->json([
            'publicKey' => config('webpush.vapid_public_key'),
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $member = $request->user()->member;
        $data = $request->validate([
            'endpoint' => 'required|url',
            'keys.auth' => 'required|string',
            'keys.p256dh' => 'required|string',
        ]);

        PushSubscription::updateOrCreate(
            ['member_id' => $member->id, 'endpoint' => $data['endpoint']],
            [
                'auth' => $data['keys']['auth'],
                'p256dh' => $data['keys']['p256dh'],
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => 'required|url']);
        PushSubscription::where('endpoint', $request->endpoint)->delete();
        return response()->json(['success' => true]);
    }
}
```

### 12. Penanganan Error + Expired Subscriptions

Di `SendPushNotification` job:
```php
try {
    $webPush = new WebPush(...);
    $webPush->queueNotification(...);
    $response = $webPush->flush();
    foreach ($response as $r) {
        if ($r->isExpired()) {
            $subscription->delete(); // endpoint expired, hapus
        }
    }
} catch (\Throwable $e) {
    // log error, jangan throw biar job lain tetep jalan
}
```

### 13. Security

- Middleware `auth`, `verified`, `role:member` di semua route push subscription
- Subscription hanya bisa dibuat/dihapus oleh member yang terautentikasi
- Broadcast push hanya dikirim ke member yang terdaftar di segmen

---

## Files yang Diubah/Dibuat

| File | Action | Keterangan |
|---|---|---|
| `.env` | Edit | Tambah VAPID_PUBLIC_KEY, VAPID_PRIVATE_KEY |
| `composer.json` | Edit | Tambah minishlink/web-push |
| `config/webpush.php` | Create | Config VAPID keys + subject |
| `database/migrations/xxxx_create_push_subscriptions_table.php` | Create | Migrasi tabel push_subscriptions |
| `app/Models/PushSubscription.php` | Create | Model push subscription |
| `app/Http/Controllers/Member/PushSubscriptionController.php` | Create | Controller subscribe/unsubscribe/vapidKey |
| `app/Jobs/SendPushNotification.php` | Create | Job kirim push notification |
| `routes/web.php` | Edit | Tambah route push subscription |
| `vite.config.ts` | Edit | Ganti generateSW → injectManifest |
| `resources/sw.ts` | Create | Custom service worker dengan push + notificationclick |
| `resources/js/composables/usePushNotification.ts` | Create | Composable push subscription |
| `resources/js/layouts/MemberLayout.vue` | Edit | Init push subscription di onMounted |
| `app/Http/Controllers/Admin/BroadcastController.php` | Edit | Tambah call SendPushNotification setelah insert notif |

---

## Asumsi & Keputusan

- **No WebSocket/Pusher**: Push notification cukup untuk member yang tidak sedang membuka app. Untuk update badge count, pake polling sederhana (nanti bisa ditambah).
- **Synchronous vs Queue**: Broadcast dikirim ke maks 100-200 member. Bisa synchronous dengan `WebPush::flush()` — jika member banyak scale up, pindah ke Queue.
- **Multiple devices**: Satu member bisa punya >1 subscription (HP + laptop). Kode handle via `updateOrCreate` + endpoint unique.
- **SW strategy injectManifest**: Workbox tetap handle precaching, kita tambah event listener di SW.
- **Tidak menambah badge/unread count polling**: Akan ditambah terpisah jika diperlukan — fokus di push delivery dulu.

---

## Verifikasi

1. `php artisan migrate` — tabel push_subscriptions terbuat
2. `php artisan config:show webpush` — VAPID keys terbaca
3. `npm run build` — service worker ter-generate dengan custom listener
4. Buka halaman member → izinkan notifikasi → subscription tersimpan di DB
5. Admin kirim broadcast notifikasi → push notification muncul di browser member
6. Klik notifikasi → terbuka halaman notifikasi
7. Hapus subscription expired — endpoint 410 Gone otomatis clean up
