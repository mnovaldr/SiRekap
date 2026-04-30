# SiRekap MCC - Sistem Rekapitulasi Event MCC

SiRekap MCC adalah sistem backend berbasis Laravel dan FilamentPHP yang digunakan untuk mengelola dan merekapitulasi data event di Malang Creative Center (MCC). Sistem ini mendukung import data dari Excel, pembersihan data otomatis (handling multi-ruangan/lantai), dan dashboard analitik yang interaktif.

## Fitur Utama
- **Dashboard Analitik**: Visualisasi data menggunakan Doughnut Chart (Kategori Ekraf, Pelaku, Kegiatan) dan Line Chart (Tren Performa).
- **Import Excel Cerdas**: Mendukung split data otomatis jika satu event menggunakan banyak ruangan atau lantai.
- **Akumulasi Peserta**: Sistem otomatis menduplikasi jumlah peserta ke setiap ruangan yang di-split untuk perhitungan total yang akurat.
- **Manajemen Event**: CRUD data event dengan status validasi (Valid, Review, Duplikat).
- **Filtering Dinamis**: Filter dashboard berdasarkan tahun dan bulan secara global.

## Persyaratan Sistem
- PHP >= 8.1
- Composer
- MySQL / MariaDB (Direkomendasikan menggunakan Laragon)
- Node.js & NPM (Untuk compile asset)

## Panduan Instalasi

### 1. Clone Repository
```powershell
git clone https://github.com/mnovaldr/SiRekap.git
cd SiRekap
```

### 2. Instalasi Dependency
```powershell
composer install
npm install
npm run build
```

### 3. Konfigurasi Environment
Salin file `.env.example` menjadi `.env` dan sesuaikan konfigurasi database Anda.
```powershell
cp .env.example .env
php artisan key:generate
```

### 4. Setup Database
Pastikan MySQL (Laragon/XAMPP) sudah berjalan. Anda bisa menggunakan script otomatis yang sudah disediakan:
```powershell
./setup-db.bat
```
*Script ini akan membuat database `mcc_rekap`, menjalankan migration, dan membuat akun admin default.*

**Akun Admin Default:**
- **Email:** admin@mcc.com
- **Password:** password

### 5. Menjalankan Aplikasi
```powershell
php artisan serve
```
Akses dashboard di: `http://localhost:8000/admin`

## Penggunaan Import Excel
1. Masuk ke menu **Import Data**.
2. Upload file Excel rekapitulasi.
3. Sistem akan mendeteksi baris dengan multi-ruangan (misal: "Studio 1, Studio 2") dan membaginya menjadi baris terpisah secara otomatis.
4. Jumlah peserta akan disalin ke setiap baris agar total di dashboard sesuai dengan akumulasi penggunaan ruangan.

## Kontribusi
Project ini dikembangkan untuk kebutuhan magang/PKL di Malang Creative Center.

## Lisensi
[MIT license](https://opensource.org/licenses/MIT).
