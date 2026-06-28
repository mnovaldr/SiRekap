# 🐳 Panduan Lengkap: Install Docker & Deploy SiRekap di Windows

Panduan ini akan membimbing Anda dari nol hingga aplikasi SiRekap berjalan menggunakan Docker di Windows.

---

## 📋 TAHAP 1: Install Docker Desktop

### 1.1 Download Docker Desktop
1. Buka browser, akses: **https://www.docker.com/products/docker-desktop/**
2. Klik tombol **"Download Docker Desktop for Windows"**.
3. Jalankan file installer yang sudah diunduh (`Docker Desktop Installer.exe`).

### 1.2 Proses Instalasi
1. Saat muncul pilihan, centang ✅ **"Use WSL 2 instead of Hyper-V"** (direkomendasikan).
2. Klik **OK** dan tunggu proses instalasi selesai (sekitar 5-10 menit).
3. Setelah selesai, klik **Close and restart** → Komputer akan restart otomatis.

### 1.3 Setup Pertama Kali Setelah Restart
1. Docker Desktop akan terbuka otomatis. Tunggu hingga ikon Docker di pojok kanan bawah berhenti beranimasi (berputar) dan berubah menjadi **stabil (diam)** → Ini tandanya Docker sudah siap.
2. Jika muncul halaman *Sign in*, Anda boleh lewati dengan klik **"Continue without signing in"**.

### 1.4 Verifikasi Instalasi
Buka **Command Prompt** atau **PowerShell**, lalu ketik:
```bash
docker --version
docker-compose --version
```
Jika muncul versi Docker (misalnya `Docker version 26.x.x`), instalasi **berhasil**!

---

## 📁 TAHAP 2: Siapkan File Docker di Project

Pastikan Anda sudah menyiapkan **4 file** berikut di dalam folder project `mcc-rekap-backend` (atau `sirekap`):

### File 1: `.dockerignore`
Buat file bernama `.dockerignore` (tanpa ekstensi) di folder utama project:
```
vendor/
node_modules/
.env
.git/
bootstrap/cache/*.php
storage/framework/views/
storage/framework/sessions/
storage/framework/cache/
storage/logs/
public/build/
public/hot
```

### File 2: `Dockerfile`
Buat file bernama `Dockerfile` (tanpa ekstensi) di folder utama project:
```dockerfile
FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git curl unzip zip libpq-dev libzip-dev libpng-dev libicu-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip gd bcmath intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN npm ci && npm run build

RUN chmod -R 777 storage bootstrap/cache

EXPOSE 8000

COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh
CMD ["/docker-entrypoint.sh"]
```

### File 3: `docker-compose.yml`
Buat file bernama `docker-compose.yml` di folder utama project:
```yaml
services:

  db:
    image: postgres:15
    container_name: sirekap_db
    restart: unless-stopped
    environment:
      POSTGRES_DB: mcc_rekap
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: secret
    ports:
      - "5438:5432"
    volumes:
      - sirekap_pgdata:/var/lib/postgresql/data

  app:
    build: .
    container_name: sirekap_app
    restart: unless-stopped
    ports:
      - "8008:8000"
    depends_on:
      - db
    environment:
      APP_ENV: local
      APP_DEBUG: "true"
      APP_URL: http://localhost:8008
      DB_CONNECTION: pgsql
      DB_HOST: db
      DB_PORT: 5432
      DB_DATABASE: mcc_rekap
      DB_USERNAME: postgres
      DB_PASSWORD: secret
      DB_SSLMODE: prefer
      SESSION_SECURE_COOKIE: "false"
      CACHE_STORE: file
      QUEUE_CONNECTION: database
      SUPABASE_URL: ""
      SUPABASE_KEY: ""

volumes:
  sirekap_pgdata:
```

### File 4: `docker-entrypoint.sh`
Buat file bernama `docker-entrypoint.sh` di folder utama project:
```bash
#!/bin/bash
set -e

echo "==> Membersihkan cache lama..."
rm -f bootstrap/cache/*.php
chmod -R 777 storage bootstrap/cache

echo "==> Menunggu database siap..."
sleep 5

echo "==> Menjalankan migrasi database..."
php artisan migrate --force

echo "==> Mendaftarkan ulang semua package..."
php artisan package:discover --ansi

echo "==> Cache ikon Filament..."
php artisan icons:cache

echo "==> Cache konfigurasi & route..."
php artisan config:cache
php artisan route:cache

echo "================================================"
echo "  ✅ SiRekap siap diakses!"
echo "  🌐 Buka: http://localhost:8008/admin"
echo "================================================"

exec php artisan serve --host=0.0.0.0 --port=8000
```

---

## 🚀 TAHAP 3: Build & Jalankan Docker

### 3.1 Buka Terminal di Folder Project
Buka **VS Code** → pilih menu **Terminal** → **New Terminal**.
Pastikan terminal sudah mengarah ke folder project Anda:
```
d:\Kuliah\Magang\SPK\mcc-rekap-backend
```

### 3.2 Build & Jalankan (Pertama Kali)
Jalankan perintah berikut:
```bash
docker-compose up -d --build
```

> ⏳ Proses ini akan memakan waktu **5-15 menit** pertama kali karena Docker perlu mengunduh base image PHP dan menginstall semua dependency. Bersabarlah!

### 3.3 Pantau Progress (Opsional)
Untuk melihat log proses startup secara real-time:
```bash
docker-compose logs -f app
```
Tekan `Ctrl + C` untuk berhenti memantau log (container tetap jalan).

### 3.4 Cek Status Container
```bash
docker ps
```
Pastikan kedua container (`sirekap_app` dan `sirekap_db`) statusnya `Up`.

---

## 🌐 TAHAP 4: Akses Aplikasi

### Akses di Laptop/PC yang Sama:
👉 Buka browser: **http://localhost:8008/admin**

### Akses dari HP/Device Lain di Jaringan yang Sama:
1. Cari IP Address PC Anda: buka CMD → ketik `ipconfig` → cari **IPv4 Address**.
2. Buka browser di HP: **http://[IP_PC_ANDA]:8008/admin**
   *(Contoh: `http://10.10.20.57:8008/admin`)*

> [!IMPORTANT]
> **Akun Login Default:**
> - Email: `admin@mcc.com`
> - Password: `password`

---

## ⚙️ TAHAP 5: Operasi Sehari-hari

### Mematikan Server (Pulang Kantor):
```bash
docker-compose down
```

### Menyalakan Kembali (Besok Pagi):
```bash
docker-compose up -d
```
*(Tidak perlu `--build` lagi karena image sudah ada, jadi langsung cepat)*

### Jika Ada Perubahan Kode:
```bash
docker-compose down
docker-compose up -d --build
```

### Melihat Log Error:
```bash
docker-compose logs app
```

---

## 🔄 TAHAP 6: Auto-Start saat Windows Menyala (Opsional)

Agar Docker dan container SiRekap otomatis berjalan setiap kali PC dinyalakan:

1. Buka **Docker Desktop** → Klik ikon ⚙️ **Settings**.
2. Di tab **General** → Centang ✅ `Start Docker Desktop when you sign in to your computer`.
3. Klik **Apply & Restart**.

Dengan pengaturan `restart: unless-stopped` di `docker-compose.yml`, container akan **otomatis hidup kembali** setiap kali Docker Desktop menyala, tanpa perlu mengetik perintah apapun!

---

## 🛠️ Troubleshooting Umum

| Error | Penyebab | Solusi |
|-------|----------|--------|
| `Port is already allocated` | Port 8008 atau 5438 sudah dipakai | Ganti angka port di `docker-compose.yml` |
| `Cannot connect to Docker daemon` | Docker Desktop belum menyala | Buka Docker Desktop, tunggu ikon stabil |
| `Connection refused` saat buka browser | Container belum selesai startup | Tunggu 30 detik, coba lagi |
| Error 419 saat login | `SESSION_SECURE_COOKIE` masih true | Sudah diset `false` di docker-compose.yml |
| Heroicon error | Cache Windows masih ada | Jalankan `docker-compose down && docker-compose up -d --build` |
