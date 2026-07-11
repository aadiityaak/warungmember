# Android Build Guide — WarungMember

## Overview

Aplikasi Android WarungMember adalah hybrid app menggunakan **Capacitor** — web app (Laravel + Inertia + Vue) dijalankan dalam WebView Android.

Push notification: **FCM (Firebase Cloud Messaging)** via `@capacitor/push-notifications`.

---

## Prasyarat

| Tool | Minimum | Cek Install |
|---|---|---|
| **Java JDK** | 17+ | `java -version` |
| **Android SDK** | Platform 36 | lihat SDK Manager |
| **Node.js** | 18+ | `node -v` |
| **Git** | - | `git --version` |

### Set Environment Variables (Windows)

```powershell
$env:ANDROID_HOME = "$env:LOCALAPPDATA\Android\Sdk"
$env:JAVA_HOME = "C:\Program Files\Java\jdk-24"
```

Cari lokasi Java:
```powershell
Get-ChildItem "C:\Program Files\Java" | Select-Object FullName
```

### Android SDK Platform 36

Pastikan Android SDK Platform 36 terinstall:

```
C:\Users\<user>\AppData\Local\Android\Sdk\platforms\android-36/
```

Kalau belum, install lewat Android Studio: **Tools → SDK Manager → SDK Platforms → centang Android 36 → Apply**.

Atau jalankan:
```powershell
# Install cmdline-tools dulu kalau belum ada
# lalu:
sdkmanager "platforms;android-36"
```

---

## Struktur Folder Android

```
android/
├── app/                          # App module
│   ├── build.gradle              # App build config
│   ├── src/main/
│   │   ├── AndroidManifest.xml   # Manifest
│   │   ├── java/.../MainActivity.java  # Entry point
│   │   └── res/                  # Icons, splash, themes
│   └── build/                    # Build output (ignored)
├── gradle/                       # Gradle wrapper
├── build.gradle                  # Root build config
├── variables.gradle              # SDK versions
├── gradle.properties             # Gradle settings
├── local.properties              # SDK path lokal (ignored)
├── gradlew / gradlew.bat         # Gradle wrapper
└── .gitignore                    # Ignore build artifacts
```

### File Konfigurasi Penting

| File | Fungsi |
|---|---|
| `capacitor.config.ts` | URL app, push notification settings |
| `android/variables.gradle` | `compileSdk`, `targetSdk`, `minSdk`, versi library |
| `android/app/build.gradle` | App ID, signing config |

### Capacitor Config (`capacitor.config.ts`)

```ts
const config: CapacitorConfig = {
  appId: 'com.warungmember.app',
  appName: 'WarungMember',
  webDir: 'capacitor-www',
  server: {
    url: 'https://domain-anda.com', // URL Laravel app
    cleartext: true,                 // Allow HTTP for dev
  },
  plugins: {
    PushNotifications: {
      presentationOptions: ['badge', 'sound', 'alert'],
    },
  },
};
```

> **`server.url`** adalah URL Laravel app yang akan di-load WebView.
> Ganti sesuai environment (dev/production).

---

## 1. Development Workflow

### Update Web App

Setelah ada perubahan di frontend Laravel, sync ke Android:

```powershell
npx cap sync android
```

Ini akan:
1. Copy web assets ke `android/app/src/main/assets/public`
2. Update config & plugin native

### Jalankan di Emulator/Device

Via Android Studio:
```powershell
npx cap open android
```
Lalu klik **Run** (▶) di Android Studio.

### Debug

- **Chrome DevTools**: Buka `chrome://inspect` di Chrome desktop, connect ke WebView
- **Logcat**: Android Studio → Logcat tab
- **Vue Devtools**: Aktif di browser, tapi tidak bisa untuk WebView native

Untuk development, lebih praktis pakai browser langsung daripada APK.

---

## 2. Build Debug APK

APK debug untuk testing, **tidak perlu signing key**.

```powershell
cd android
.\gradlew.bat assembleDebug
```

Output:
```
android/app/build/outputs/apk/debug/app-debug.apk
```

Install ke HP: kirim file APK, buka, tap Install.

> Setting HP: **Settings → Security → Install unknown apps** → aktifkan.

---

## 3. Build Production (Release)

APK release butuh **keystore** (signing key) dan konfigurasi **ProGuard** (opsional).

### 3.1 Generate Keystore

Buat signing key (sekali saja):

```powershell
keytool -genkey -v -keystore release.keystore -alias warungmember `
  -keyalg RSA -keysize 2048 -validity 10000 -storepass android -keypass android
```

Letakkan `release.keystore` di `android/app/`.

### 3.2 Konfigurasi Signing di `android/app/build.gradle`

```groovy
android {
    // ... existing config

    signingConfigs {
        release {
            storeFile file('release.keystore')
            storePassword 'android'
            keyAlias 'warungmember'
            keyPassword 'android'
        }
    }

    buildTypes {
        release {
            minifyEnabled true
            proguardFiles getDefaultProguardFile('proguard-android-optimize.txt'), 'proguard-rules.pro'
            signingConfig signingConfigs.release
        }
    }
}
```

### 3.3 Build Release APK

```powershell
cd android
.\gradlew.bat assembleRelease
```

Output:
```
android/app/build/outputs/apk/release/app-release.apk
```

### 3.4 Build AAB (Android App Bundle — untuk Play Store)

```powershell
cd android
.\gradlew.bat bundleRelease
```

Output:
```
android/app/build/outputs/bundle/release/app-release.aab
```

> AAB adalah format yang **wajib** untuk upload ke Google Play Store.

---

## 4. Push Notification (FCM)

Push notification menggunakan **Firebase Cloud Messaging**.

### Setup Firebase

1. Buka [console.firebase.google.com](https://console.firebase.google.com)
2. Tambah project → **Android** → Package name: `com.warungmember.app`
3. Download `google-services.json`
4. Taruh di: `android/app/google-services.json`

### Dapatkan Server Key

Firebase Console → Project Settings → Cloud Messaging → **Server Key**

Set di `.env` Laravel:
```env
FCM_SERVER_KEY=your_fcm_server_key_here
```

### Build Ulang APK

Setelah `google-services.json` terpasang, rebuild APK agar FCM teraktivasi.

**Catatan**: `google-services.json` jangan di-commit ke git (sudah di `.gitignore`).

---

## 5. Update Aplikasi

### Update Web Content

Web app di-load dari `server.url` — update Laravel app di server, restart, perubahan langsung muncul di app. **Tidak perlu rebuild APK**.

### Update Native Code

Kalau ada perubahan di:
- `capacitor.config.ts`
- Plugin baru
- Konfigurasi AndroidManifest
- Versi SDK/library

Maka perlu:
```powershell
npx cap sync android
cd android
.\gradlew.bat assembleDebug  # atau assembleRelease
```

---

## Command Quick Reference

```powershell
# Sync web assets ke Android
npx cap sync android

# Buka di Android Studio
npx cap open android

# Build debug APK
cd android
.\gradlew.bat assembleDebug

# Build release APK
.\gradlew.bat assembleRelease

# Build AAB (Play Store)
.\gradlew.bat bundleRelease

# Bersihkan build
.\gradlew.bat clean

# Skip test kalo mau lebih cepat
.\gradlew.bat assembleDebug -x test
```

---

## Troubleshooting

### `android-36` tidak ditemukan
Install platform 36 lewat SDK Manager, atau turunkan `compileSdk` di `variables.gradle`.

### Storage symlink error saat `cap sync`
`public/storage` adalah junction Windows. Solusi: `webDir` di `capacitor.config.ts` arahkan ke folder lain (misal `capacitor-www`).

### Build lambat
Build pertama selalu lambat. Build berikutnya lebih cepat karena Gradle daemon.

### JAVA_HOME error
```powershell
# Cari path JDK
(Get-Command java).Source
# Set JAVA_HOME ke root JDK (bukan folder bin)
$env:JAVA_HOME = "C:\Program Files\Java\jdk-24"
```

### Push notification tidak muncul
Pastikan:
- `google-services.json` ada di `android/app/`
- `FCM_SERVER_KEY` terisi di `.env`
- Izin notifikasi diaktifkan di HP
- Token FCM terkirim ke server (cek tabel `push_subscriptions`)

---

## Referensi

- [Capacitor Documentation](https://capacitorjs.com/docs)
- [Capacitor Push Notifications](https://capacitorjs.com/docs/apis/push-notifications)
- [Firebase Console](https://console.firebase.google.com)
