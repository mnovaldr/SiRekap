# 🏛️ SiRekap MCC - Premium Analytics Dashboard

SiRekap MCC adalah sistem backend analitik dan manajemen data event untuk **Malang Creative Center (MCC)**. Dibangun menggunakan ekosistem **Laravel 11**, **FilamentPHP v3**, dan **PostgreSQL**, aplikasi ini dirancang untuk performa tinggi, stabilitas produksi, dan visualisasi data yang memukau.

---

## 🚀 Fitur Utama
- **📊 Interactive Analytics**: Dashboard dengan chart dinamis (Trend Harian, Bulanan, Tahunan).
- **📉 Analitik Ekonomi Kreatif**: Laporan mendalam per sub-sektor dan kategori pelaku ekraf.
- **⚡ High Performance**: Kecepatan load halaman < 1.5 detik berkat optimasi indeks composite & caching.
- **🐘 PostgreSQL Ready**: Sepenuhnya kompatibel dengan Supabase & Railway (Strict Typing).
- **📥 Intelligent Import**: Sistem import Excel yang cerdas dengan deteksi duplikat & pembersihan data.
- **📄 Professional Export**: Export data ke Excel & PDF dengan template resmi MCC.

---

## 🛠️ Persyaratan Sistem
- **PHP**: >= 8.2 (Direkomendasikan 8.3)
- **Database**: PostgreSQL (Produksi) / MySQL (Lokal)
- **Runtime**: Node.js & NPM untuk aset Filament
- **Tools**: Composer, Git

---

## ⚙️ Panduan Instalasi Cepat

### 1. Clone & Dependencies
```bash
git clone https://github.com/mnovaldr/SiRekap.git
cd SiRekap
composer install
npm install && npm run build
```

### 2. Konfigurasi Environment
```bash
cp .env.example .env
php artisan key:generate
```
Sesuaikan `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di file `.env`.

### 3. Migrasi & Admin
```bash
php artisan migrate --seed
```
*Gunakan `php artisan make:filament-user` jika ingin membuat akun admin tambahan.*

---

## 🐘 Optimasi Produksi (PostgreSQL)

Aplikasi ini telah dioptimalkan khusus untuk **PostgreSQL** (Supabase/Railway). Berikut adalah langkah penting untuk menjaga stabilitas:

### Indeks Database (Composite Indexes)
Untuk performa query analitik yang cepat, pastikan indeks berikut aktif:
- `(status_data, tanggal)`
- `(status_data, is_primary_row, tanggal)`
- `(status_data, tanggal, sub_sektor)`
- `(status_data, bulan)`

### Maintenance Rutin
Jika data pada chart atau rekap bulanan tidak muncul, jalankan fitur perbaikan di halaman **Pengaturan > Kelola Database**:
1. **Fix Chart (Recalculate Primary)**: Menandai satu baris utama per event untuk statistik akurat.
2. **Recalculate Keys**: Mengisi kolom `bulan` dan kunci unik data.

---

## ⚡ Performa & Caching

Sistem menggunakan strategi caching berlapis untuk meminimalkan beban database:
- **Cache Store**: Gunakan `file` atau `redis` (jangan `database`).
- **TTL Cache**: Dashboard (30 min), Analitik (15 min), Stats (5 min).

**Perintah Optimasi:**
```bash
php artisan optimize           # Cache config, routes, & views
php artisan filament:optimize  # Cache filament components
php artisan cache:clear        # Bersihkan cache jika data tidak update
```

---

## 🔧 Troubleshooting

| Masalah | Solusi |
|---------|--------|
| **Error 500 (SQL Type Mismatch)** | Pastikan query menggunakan `whereRaw` dengan double quotes untuk nama kolom. |
| **Chart Kosong (0)** | Jalankan **Fix Chart** di menu Kelola Database untuk mengisi `is_primary_row`. |
| **Halaman Lambat** | Matikan `APP_DEBUG`, aktifkan `php artisan optimize`, dan periksa indeks database. |
| **Error Import** | Pastikan file Excel sesuai template dan kolom tanggal memiliki format yang benar. |

---

## 📜 Lisensi & Kontributor
- **Lisensi**: [MIT License](https://opensource.org/licenses/MIT)
- **Developer**: [mnovaldr](https://github.com/mnovaldr)
- **Optimizer**: Antigravity AI

---

**Malang Creative Center — Creative Culture Ambience**  
*Membangun Ekosistem Ekonomi Kreatif yang Terukur dan Berkelanjutan.*
