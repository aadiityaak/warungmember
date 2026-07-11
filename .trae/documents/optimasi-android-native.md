# Plan Optimasi Android - Lebih Native & Optimal

## Ringkasan

Aplikasi Android saat ini menggunakan Capacitor v8 sebagai WebView wrapper. Banyak aspek native yang belum dikonfigurasi optimal — mulai dari resource dasar yang hilang hingga plugin native untuk pengalaman lebih mulus.

## Current State

- **Capacitor v8** — remote server mode (load `https://prototype8.sweet.web.id`)
- **1 plugin**: PushNotifications
- **Tidak ada** `colors.xml` — styles.xml refer `@color/colorPrimary` dkk ke resource tak ada
- **Duplikasi** MainActivity: `com.getcapacitor.myapp` (sisa template, tidak dipakai)
- **Tidak ada** StatusBar, NavigationBar, Haptics, Keyboard plugin
- **Tidak ada** edge-to-edge, back button handler, orientation lock
- Hanya 1 permission: INTERNET

---

## P1 — Critical (bug & foundation)

### 1. Buat `colors.xml`

**File:** `android/app/src/main/res/values/colors.xml`

**Why:** styles.xml refer `@color/colorPrimary`, `@color/colorPrimaryDark`, `@color/colorAccent` yang tidak ada. Ini potensi crash di runtime.

**What:**
```xml
<resources>
    <color name="colorPrimary">#26A69A</color>    <!-- teal 400 -->
    <color name="colorPrimaryDark">#00897B</color> <!-- teal 600 -->
    <color name="colorAccent">#FF6F00</color>      <!-- amber 900 -->
    <color name="splashBackground">#26A69A</color>
    <color name="statusBarColor">#00897B</color>
    <color name="navBarColor">#FFFFFF</color>
    <color name="navBarLight">true</color>
</resources>
```

---

### 2. Hapus Duplikat MainActivity

**File:** `android/app/src/main/java/com/getcapacitor/myapp/MainActivity.java`

**Why:** Sisa template default, tidak dirujuk oleh AndroidManifest. Namespace yang dipakai adalah `com.warungmember.app`.

**Action:** Delete file.

---

### 3. Install & Konfigurasi StatusBar Plugin

**Why:** Status bar yang sesuai tema membuat app terasa native. Tanpa ini, status bar tetap warna system default (hitam/putih) tidak cocok dengan tema aplikasi.

**Steps:**
- `npm install @capacitor/status-bar`
- `npx cap sync`
- Tambah kode di `resources/js/app.ts` untuk set style status bar sesuai tema (transparent, light/dark icons)

**Konfigurasi:**
```ts
import { StatusBar, Style } from '@capacitor/status-bar';

// Di app.ts, setelah app.mount()
if (Capacitor.isNativePlatform()) {
    await StatusBar.setStyle({ style: Style.Dark });
    await StatusBar.setBackgroundColor({ color: '#00897B' });
}
```

---

### 4. Konfigurasi Edge-to-Edge

**Why:** Android 15+ mewajibkan edge-to-edge. Tanpa ini, app tampak outdated dengan batas hitam di sistem bar.

**Files:** `android/app/src/main/res/values/styles.xml` + `android/app/src/main/AndroidManifest.xml`

**What:**
- Ubah theme parent ke `Theme.Material3.DayNight.NoActionBar` (lebih modern)
- Tambah `android:windowOptOutEdgeToEdgeEnforcement="false"` atau set `android:fitsSystemWindows` di WebView
- Opsional: tambah CSS `env(safe-area-inset-*)` handling di frontend

---

## P2 — Native Feel (medium effort)

### 5. Install & Konfigurasi Haptics Plugin

**Why:** Feedback getar pada interaksi (klik tombol, swipe, pull-to-refresh) sangat meningkatkan persepsi native.

**Steps:**
- `npm install @capacitor/haptics`
- `npx cap sync`
- Integrasi di komponen frontend (tombol, pull-to-refresh, notifikasi)

**Penggunaan di frontend:**
```ts
import { Haptics, ImpactStyle } from '@capacitor/haptics';

// Di komponen tombol
async function onButtonPress() {
    if (Capacitor.isNativePlatform()) {
        await Haptics.impact({ style: ImpactStyle.Medium });
    }
    // ... action
}
```

---

### 6. Install & Konfigurasi Keyboard Plugin + manifest

**Why:** Keyboard native yang smooth — tanpa konfigurasi, WebView tidak menyesuaikan layout saat keyboard muncul, form bisa tertutup.

**Steps:**
- `npm install @capacitor/keyboard`
- `npx cap sync`
- Tambah `android:windowSoftInputMode="adjustResize"` di AndroidManifest `<activity>`
- Register listener keyboard show/hide untuk adjust layout

---

### 7. Lock Screen Orientation ke Portrait

**Why:** PWA manifest sudah set `orientation: 'portrait'`. Perlu di-enforce di native juga agar konsisten.

**File:** `android/app/src/main/AndroidManifest.xml`

**What:** Tambah `android:screenOrientation="portrait"` di `<activity>`.

---

### 8. Back Button Handling (App Plugin)

**Why:** Tanpa handler, tekan back di halaman utama langsung exit app tanpa konfirmasi. Native app biasanya konfirmasi "exit?" atau minimal minimize.

**Steps:**
- `npm install @capacitor/app`
- `npx cap sync`
- Di frontend:
  - Di halaman utama: show dialog konfirmasi "Keluar aplikasi?"
  - Di halaman lain: navigate back (history.back)

```ts
import { App } from '@capacitor/app';

App.addListener('backButton', ({ canGoBack }) => {
    if (canGoBack) {
        window.history.back();
    } else {
        // Show confirm dialog
        Dialog.confirm({
            title: 'Keluar',
            message: 'Yakin ingin keluar aplikasi?',
        }).then(({ value }) => {
            if (value) App.exitApp();
        });
    }
});
```

---

### 9. Styling Navigation Bar

**Why:** Navigation bar (gesture/3-button) di bagian bawah Android perlu warna yang cocok dengan tema.

**File:** `android/app/src/main/res/values/styles.xml`

**What:** Tambah:
```xml
<item name="android:navigationBarColor">@color/navBarColor</item>
<item name="android:windowLightNavigationBar">true</item>
```

---

## P3 — Polish & Production

### 10. Splash Screen Branding

**Why:** Saat ini splash hanya background `@drawable/splash` tanpa brand color yang proper. Android 12+ SplashScreen API bisa dikustom dengan icon + background color.

**File:** `android/app/src/main/res/values/styles.xml`

**What:** Ubah `Theme.SplashScreen` parent dan set properti:
```xml
<style name="AppTheme.NoActionBarLaunch" parent="Theme.SplashScreen">
    <item name="windowSplashScreenBackground">@color/splashBackground</item>
    <item name="windowSplashScreenAnimatedIcon">@mipmap/ic_launcher</item>
    <item name="postSplashScreenTheme">@style/AppTheme.NoActionBar</item>
</style>
```

---

### 11. App Icon Upgrade

**Why:** Icon saat ini emoticon smile — kurang profesional untuk app bisnis membership/kasir.

**Action:** File terpisah — perlu desain icon baru oleh designer. Tercatat sebagai catatan saja.

---

### 12. Deep Linking Setup

**Why:** Memungkinkan link (contoh: `https://warungmember.com/payment/xxx`) langsung buka app.

**File:** `AndroidManifest.xml`

**What:** Tambah intent-filter kedua di `<activity>`:
```xml
<intent-filter android:autoVerify="true">
    <action android:name="android.intent.action.VIEW" />
    <category android:name="android.intent.category.DEFAULT" />
    <category android:name="android.intent.category.BROWSABLE" />
    <data android:scheme="https" android:host="warungmember.com" />
</intent-filter>
```

---

### 13. Bundle Web Assets untuk Production

**Why:** Saat ini app di mode `server.url` — selalu online. Untuk production, assets harus di-bundle lokal agar bisa offline dan loading lebih cepat.

**Action:** Di `capacitor.config.ts`, buat 2 mode — development (remote) dan production (local). Atau gunakan env variable.

---

### 14. ProGuard/R8 Minification

**Why:** `minifyEnabled false` di build.gradle. Enable untuk production mengurangi ukuran APK dan obfuscate code.

**File:** `android/app/build.gradle`

```groovy
release {
    minifyEnabled true
    proguardFiles getDefaultProguardFile('proguard-android-optimize.txt'), 'proguard-rules.pro'
}
```

---

## Prioritas & Estimasi

| # | Item | Prioritas | Effort | Dampak Native |
|---|------|-----------|--------|---------------|
| 1 | colors.xml | **P1** | 5 menit | Tinggi (fix bug) |
| 2 | Hapus duplikat MainActivity | **P1** | 1 menit | Rendah (cleanup) |
| 3 | StatusBar plugin | **P1** | 15 menit | Tinggi |
| 4 | Edge-to-edge | **P1** | 20 menit | Tinggi |
| 5 | Haptics plugin | **P2** | 15 menit | Sedang |
| 6 | Keyboard plugin + manifest | **P2** | 15 menit | Tinggi |
| 7 | Lock orientation | **P2** | 2 menit | Sedang |
| 8 | Back button handler | **P2** | 20 menit | Tinggi |
| 9 | Navigation bar styling | **P2** | 5 menit | Sedang |
| 10 | Splash branding | **P3** | 10 menit | Sedang |
| 11 | App icon | **P3** | - | Sedang (desain) |
| 12 | Deep linking | **P3** | 15 menit | Rendah |
| 13 | Bundle lokal | **P3** | 30 menit | Tinggi |
| 14 | ProGuard | **P3** | 10 menit | Rendah (opsional) |

---

## Dependencies Baru (npm)

```json
"@capacitor/status-bar": "^8.0.0",
"@capacitor/haptics": "^8.0.0",
"@capacitor/keyboard": "^8.0.0",
"@capacitor/app": "^8.0.0"
```

Semua plugin ini resmi dari Capacitor team, backward compatible dengan Capacitor v8.

---

## Verification

1. `npx cap sync` sukses tanpa error
2. App build sukses: `cd android && ./gradlew assembleDebug`
3. Status bar berwarna teal, icon putih
4. Navigation bar putih dengan tombol dark
5. Getar saat tap tombol (Haptics)
6. Keyboard tidak menutup input form
7. Back tombol: di halaman utama show confirm dialog
8. Screen orientation locked portrait
9. Service worker & push notification masih jalan
10. App tidak crash di Android 12, 13, 14, 15
