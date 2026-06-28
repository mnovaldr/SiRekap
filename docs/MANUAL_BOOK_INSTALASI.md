# 📘 MANUAL BOOK: Panduan Instalasi & Deploy Lokal SiRekap MCC

Dokumen ini merupakan panduan resmi (Dokumen Serah Terima) untuk melakukan instalasi dan pengaturan aplikasi **SiRekap MCC (Malang Creative Center)** di lingkungan lokal/server internal (Windows).

Dengan mengikuti panduan ini, aplikasi akan terkonfigurasi secara otomatis menyala setiap kali komputer/server dihidupkan (*Auto-Start*).

---

## 🖥️ 1. Persyaratan Sistem (System Requirements)
Pastikan komputer/server yang digunakan memenuhi spesifikasi perangkat lunak berikut:
1. **Sistem Operasi**: Windows 10 / 11 (64-bit) atau Windows Server.
2. **Web Server & PHP**: **[Laragon](https://laragon.org/download/)** (Disarankan versi Full).
   - PHP minimal versi **8.2** atau **8.3**.
3. **Database Server**: **[PostgreSQL 15 atau 16](https://www.enterprisedb.com/downloads/postgres-postgresql-downloads)**.
   - *(Aplikasi ini secara khusus dioptimalkan untuk performa PostgreSQL, mohon tidak menggunakan MySQL).*
4. **Dependency Manager**: 
   - **[Composer](https://getcomposer.org/download/)** (Untuk package PHP).
   - **[Node.js & NPM](https://nodejs.org/en/download/)** (Untuk kompilasi aset Frontend).
5. **Git**: **[Git for Windows](https://git-scm.com/downloads)** (Untuk mengambil *source code*).

---

## 🐘 2. Instalasi Database & Ekstensi (PostgreSQL)

### A. Instalasi PostgreSQL Core
1. Jalankan installer PostgreSQL yang telah diunduh.
2. Lakukan instalasi standar (Next).
3. **PENTING:** Saat muncul form pembuatan **Password**, masukkan password (contoh: `root` atau `secret`). Catat password ini.
4. Jika installer menyarankan port **5433**, biarkan saja (jangan dirubah) untuk menghindari bentrok dengan service lain. Jika default-nya 5432, biarkan 5432.
5. Pada pilihan komponen *Stack Builder*, Anda dapat **menghilangkan centangnya** (tidak diperlukan).
6. Selesaikan instalasi. (*PostgreSQL akan otomatis berjalan di background Windows (Windows Services) setiap kali komputer menyala*).

### B. Setup Database `mcc_rekap`
1. Buka aplikasi **pgAdmin 4** (terinstal otomatis bersama PostgreSQL).
2. Masukkan password PostgreSQL Anda.
3. Buka menu sebelah kiri: **Servers -> PostgreSQL -> Databases**.
4. **Klik kanan** pada folder Databases -> Pilih **Create** -> **Database...**
5. Isi *Database Name* dengan: `mcc_rekap`, lalu klik **Save**.

### C. Mengaktifkan PostgreSQL di Laragon
1. Buka aplikasi **Laragon**.
2. **Klik Kanan** di area kosong Laragon -> Pilih **PHP** -> **Quick Settings** -> Klik **pdo_pgsql**.
3. **Klik Kanan** kembali -> Pilih **PHP** -> **Quick Settings** -> Klik **pgsql**.
4. Tekan tombol **Stop** lalu **Start** di Laragon untuk merestart *service* Apache/Nginx.

---

## 📦 3. Instalasi Source Code Project

1. Pindahkan atau *clone* *source code* aplikasi ini ke dalam folder root Laragon, lalu **ubah nama foldernya menjadi `sirekap`**. Lokasinya harus berada di:
   `C:\laragon\www\sirekap`
2. Buka folder `sirekap` menggunakan Text Editor (seperti VS Code atau Notepad).
3. Salin file `.env.example` dan ubah namanya menjadi `.env`.
4. Sesuaikan bagian *Database* dan *App URL* di dalam file `.env` menjadi seperti berikut:
   ```env
   APP_ENV=local
   APP_DEBUG=false
   APP_URL=http://sirekap.test
   
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5433         # Sesuaikan dengan port saat instalasi (5433 atau 5432)
   DB_DATABASE=mcc_rekap
   DB_USERNAME=postgres
   DB_PASSWORD=secret   # Ganti dengan password instalasi Tahap 2A
   DB_SSLMODE=prefer
   
   # Kosongkan nilai Supabase agar fitur Backup tersimpan secara offline di lokal
   SUPABASE_URL=
   SUPABASE_KEY=
   ```

---

## 🚀 4. Build, Migrasi, & Eksekusi Pertama

Buka **Terminal** (CMD / PowerShell) arahkan ke dalam folder project (`C:\laragon\www\sirekap`), lalu jalankan urutan perintah berikut:

1. **Install dependensi sistem backend (PHP):**
   ```bash
   composer install
   ```
2. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```
3. **Install dependensi antarmuka (Frontend):**
   ```bash
   npm install
   ```
4. **Kompilasi aset antarmuka permanen (Build):**
   ```bash
   npm run build
   ```
   *(Langkah kompilasi ini penting agar aplikasi bisa langsung dibuka tanpa harus menjalankan terminal Vite/Node.js terus menerus).*
5. **Migrasi struktur database & Isi Data Awal:**
   ```bash
   php artisan migrate:fresh --seed
   ```

---

## ⚙️ 5. Pengaturan Auto-Start (Server Menyala Otomatis)

Agar pihak kantor/tempat magang tidak perlu membuka *terminal* atau menyalakan aplikasi secara manual setiap hari:

1. Buka aplikasi **Laragon**.
2. Klik ikon **Gear (Pengaturan / Settings)** di pojok kanan atas.
3. Centang tiga konfigurasi berikut:
   - `[x] Run Laragon when Windows starts` *(Laragon menyala otomatis saat komputer hidup)*.
   - `[x] Run minimized` *(Berjalan diam-diam di background / system tray)*.
   - `[x] Auto start` *(Otomatis menghidupkan Web Server).*
4. Tutup jendela pengaturan.

Dengan kombinasi PostgreSQL (Windows Service) dan Laragon Auto-Start, **server langsung siap dipakai sesaat setelah komputer menyala!**

---

## 🔐 6. Akses Sistem & Akun Default

Untuk menggunakan aplikasi, cukup buka Browser (Google Chrome, Edge, dll) di komputer server atau perangkat di jaringan yang sama, lalu akses URL lokal Laragon:

👉 **URL Akses:** [http://sirekap.test/admin](http://sirekap.test/admin)

> [!IMPORTANT]
> **Data Akun Administrator (Bawaan Seeder):**
> - **Email**: `admin@mcc.com`
> - **Password**: `password`

*(Mohon segera lakukan perubahan password atau buat akun admin baru setelah berhasil *login* untuk pertama kalinya demi keamanan data instansi).*
