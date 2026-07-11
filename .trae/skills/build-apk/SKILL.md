---
name: "build-apk"
description: "Builds WarungMember Android APK via Capacitor. Invoke when user asks to build APK, generate APK, compile Android app, or setup Capacitor build environment from scratch."
---

# Build APK WarungMember

Build APK debug untuk aplikasi Android WarungMember menggunakan Capacitor.

## Prasyarat

Semua tools ini harus ada di sistem sebelum build:

| Tool | Lokasi Umum |
|---|---|
| **Java JDK 17+** | `C:\Program Files\Java\jdk-<version>` |
| **Android SDK** | `C:\Users\<user>\AppData\Local\Android\Sdk` |
| **Android SDK Platform 36** | Diinstal via SDK Manager |
| **Node.js & npm** | Dari PATH sistem |

## Langkah Build

### 1. Set Environment Variables

```powershell
$env:ANDROID_HOME = "C:\Users\ASUS\AppData\Local\Android\Sdk"
$env:JAVA_HOME = "C:\Program Files\Java\jdk-24"
```

Cari lokasi Java yang terinstall:
```powershell
Get-ChildItem "C:\Program Files\Java" | Select-Object FullName
```

Cari lokasi Android SDK:
```powershell
Get-ChildItem "$env:LOCALAPPDATA\Android\Sdk" -ErrorAction SilentlyContinue
```

### 2. Verifikasi Android SDK Platform 36

Periksa apakah platform 36 sudah terinstall:
```powershell
ls "$env:LOCALAPPDATA\Android\Sdk\platforms"
```

Kalau belum ada `android-36`, coba install lewat Gradle (otomatis download saat build), atau install manual lewat Android Studio: **Tools → SDK Manager → SDK Platforms → centang Android 36 → Apply**.

### 3. Update Capacitor Config

File: `capacitor.config.ts` — pastikan `server.url` mengarah ke URL Laravel app yang benar:

```ts
server: {
  url: 'https://domain-anda.com',
}
```

Setelah ganti URL, sync:
```powershell
cd D:\dev\warungmember
npx cap sync android
```

### 4. Build APK

```powershell
$env:ANDROID_HOME = "C:\Users\ASUS\AppData\Local\Android\Sdk"
$env:JAVA_HOME = "C:\Program Files\Java\jdk-24"
cd D:\dev\warungmember\android
.\gradlew.bat assembleDebug
```

Build pertama butuh ~3-5 menit (download dependencies). Build berikutnya lebih cepat.

### 5. Lokasi Output APK

```
D:\dev\warungmember\android\app\build\outputs\apk\debug\app-debug.apk
```

Ukuran: ~8 MB.

### 6. Install ke HP

Kirim file `app-debug.apk` ke HP Android, buka file-nya, dan install. Pastikan **"Install dari sumber tidak dikenal"** diaktifkan.

## Troubleshooting

### `android-36` not found
Install SDK platform 36:
```powershell
# Cek apakah sdkmanager ada
Get-ChildItem "$env:LOCALAPPDATA\Android\Sdk" -Recurse -Filter "sdkmanager*"

# Kalau ada, jalankan
& "C:\Users\ASUS\AppData\Local\Android\Sdk\cmdline-tools\latest\bin\sdkmanager.bat" "platforms;android-36"
```

Atau turunkan `compileSdk` di `android/variables.gradle` ke versi yang sudah terinstall (contoh: 35), lalu turunkan juga versi `androidx.core` di file yang sama agar kompatibel.

### Storage symlink error
```powershell
# D:\dev\warungmember\public\storage adalah junction (symlink).
# Capacitor copy gagal kena junction. Solusi: webDir di capacitor.config.ts
# arahkan ke folder tanpa junction, misal 'capacitor-www'.
```

### Gradle build lambat
Build pertama selalu lambat. Build berikutnya akan lebih cepat karena Gradle daemon sudah running.

### JAVA_HOME error
```powershell
# Cari path Java yang benar
(Get-Command java).Source
# Set JAVA_HOME ke folder root JDK (bukan folder bin)
$env:JAVA_HOME = "C:\Program Files\Java\jdk-24"
```
