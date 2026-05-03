# SiRekap MCC - Sistem Rekapitulasi Event MCC

SiRekap MCC adalah sistem backend berbasis Laravel dan FilamentPHP yang digunakan untuk mengelola dan merekapitulasi data event di Malang Creative Center (MCC). Sistem ini mendukung import data dari Excel, pembersihan data otomatis (handling multi-ruangan/lantai), dan dashboard analitik yang interaktif.

## 🚀 Fitur Utama
- **Dashboard Analitik**: Visualisasi data interaktif (Doughnut Chart, Bar Chart, Line Chart).
- **Import Excel Cerdas**: Split data otomatis untuk multi-ruangan/lantai dan akumulasi peserta.
- **Export Multi-Format**: Dukungan export ke Excel, CSV, JSON, dan PDF profesional.
- **Manajemen Event**: CRUD lengkap dengan deteksi duplikat otomatis.
- **Analitik Ekonomi Kreatif**: Laporan mendalam per sub-sektor dan kategori pelaku.

## 🛠️ Persyaratan Sistem
- PHP >= 8.1
- Composer
- MySQL / MariaDB (Direkomendasikan menggunakan Laragon)
- Node.js & NPM

## ⚙️ Panduan Instalasi

### 1. Clone & Install
```powershell
git clone https://github.com/mnovaldr/SiRekap.git
cd SiRekap
composer install
npm install && npm run build
```

### 2. Konfigurasi
```powershell
cp .env.example .env
php artisan key:generate
```

### 3. Setup Database
Pastikan MySQL sudah berjalan, lalu jalankan script otomatis:
```powershell
./setup-db.bat
```
*Script ini akan membuat database `mcc_rekap`, menjalankan migration, dan membuat akun admin.*

**Akun Admin Default:**
- **Email:** `admin@mcc.com`
- **Password:** `password`

### 4. Menjalankan Aplikasi
```powershell
php artisan serve
```
Akses dashboard di: `http://localhost:8000/admin`

## 📚 Dokumentasi Lengkap
Informasi lebih detail mengenai fitur-fitur sistem dapat ditemukan di folder `docs/`:
- [📊 Fitur Dashboard](docs/DASHBOARD_FEATURES.md)
- [📈 Analitik Ekonomi Kreatif](docs/EXPORT_ANALYTICS_FEATURE.md)
- [📥 Panduan Export Data](docs/EXPORT_FEATURE_GUIDE.md)
- [📄 Spesifikasi Template PDF](docs/PDF_TEMPLATE_FINAL.md)

## ⚖️ Lisensi
[MIT license](https://opensource.org/licenses/MIT).
