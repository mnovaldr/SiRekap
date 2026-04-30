# 🚀 Setup MCC REKAP Backend (Laravel + Filament)

## ⚡ Quick Setup (Otomatis)

### 1. Pastikan Laragon Running
- Buka **Laragon**
- Klik **Start All** (Apache + MySQL harus hijau)

### 2. Jalankan Setup Script
Double-click file: **`setup-db.bat`**

Script ini akan:
- ✅ Detect MySQL di Laragon
- ✅ Buat database `mcc_rekap`
- ✅ Jalankan migrations
- ✅ Seed data master

**Jika berhasil, Anda akan lihat:**
```
========================================
SETUP BERHASIL!
========================================
Database: mcc_rekap
Admin: admin@mcc.com
Password: password
========================================
```

---

## 🔧 Manual Setup (Jika Script Gagal)

### Langkah 1: Buat Database

**Via HeidiSQL:**
1. Laragon > Menu > MySQL > HeidiSQL
2. Klik kanan connection > Create new > Database
3. Nama: `mcc_rekap`
4. Collation: `utf8mb4_unicode_ci`
5. Klik OK

**Via phpMyAdmin:**
1. Laragon > Menu > MySQL > phpMyAdmin
2. Tab "Databases"
3. Database name: `mcc_rekap`
4. Collation: `utf8mb4_unicode_ci`
5. Klik Create

### Langkah 2: Jalankan Migration

Buka terminal di folder `mcc-rekap-backend`:

```bash
php artisan migrate:fresh --seed
```

---

## 📊 Verifikasi Setup

### Cek Database di HeidiSQL/phpMyAdmin

**Tables yang harus ada:**
- ✅ users (1 row)
- ✅ master_kategori (25 rows)
- ✅ master_lokasi (14 rows)
- ✅ organizers (0 rows)
- ✅ events (0 rows)
- ✅ activity_logs (0 rows)
- ✅ cache, cache_locks, sessions, jobs, job_batches, failed_jobs

### Test Login

```bash
php artisan tinker
```

Lalu jalankan:
```php
User::first()
// Harus return: admin@mcc.com
```

---

## 🎨 Install Filament (Setelah Database Setup)

### 1. Fix PHP Extensions (PENTING!)

**Masalah:** Filament butuh `intl` extension, tapi diblokir Application Control.

**Solusi Cepat:**
```bash
composer require filament/filament:"^3.3" --ignore-platform-req=ext-intl --ignore-platform-req=ext-exif
```

**Solusi Permanen:**
1. Buka `C:\laragon\bin\php\php-8.5.4-nts-Win32-vs17-x64\php.ini`
2. Cari baris:
   ```ini
   extension=intl
   extension=exif
   ```
3. Pastikan TIDAK ada `;` di depan (sudah uncommented)
4. Restart Laragon

### 2. Install Filament

```bash
composer require filament/filament:"^3.3" --with-all-dependencies
```

Jika error intl, pakai flag `--ignore-platform-req=ext-intl`

### 3. Generate Resources

**CATATAN:** Event Resource sudah dibuat manual dengan fitur lengkap!

Untuk Master Data Resources (opsional):
```bash
php artisan make:filament-resource MasterKategori --generate
php artisan make:filament-resource MasterLokasi --generate
php artisan make:filament-resource Organizer --generate
```

### 4. Generate Custom Pages (Opsional)

**CATATAN:** Halaman Cek Duplikat sudah dibuat!

Untuk halaman lainnya (akan dikembangkan):
```bash
php artisan make:filament-page ImportData
php artisan make:filament-page ExportData
php artisan make:filament-page RekapBulanan
php artisan make:filament-page AnalitikEkraf
```

### 5. Generate Dashboard Widgets

**CATATAN:** Widgets sudah dibuat manual, tidak perlu generate lagi.

Widgets yang sudah tersedia:
- ✅ `StatsOverview` - KPI cards (Total Event, Peserta, Ekraf Unik, % Non Komersial)
- ✅ `EventsChart` - Bar chart tren bulanan
- ✅ `DuplicateAlert` - Alert untuk data duplikat/review

**Event Resource juga sudah dibuat:**
- ✅ `EventResource` - CRUD lengkap dengan form & table
- ✅ Form dengan 13 fields (sesuai requirements)
- ✅ Table dengan filters (Status, Bidang, Tanggal)
- ✅ Actions: Edit, Delete, Validate
- ✅ Status badges (Valid/Review/Duplikat)

**Halaman Cek Duplikat:**
- ✅ `CekDuplikat` page (placeholder, akan dikembangkan)

---

## 🌐 Jalankan Development Server

```bash
php artisan serve
```

Akses: **http://localhost:8000/admin**

**Login:**
- Email: `admin@mcc.com`
- Password: `password`

---

## ⚠️ Troubleshooting

### Error: "Unknown database 'mcc_rekap'"
→ Database belum dibuat. Jalankan `setup-db.bat` atau buat manual via HeidiSQL.

### Error: "Could not open input file: artisan"
→ Anda tidak di folder `mcc-rekap-backend`. Jalankan:
```bash
cd mcc-rekap-backend
```

### Error: "Access denied for user 'root'"
→ Password MySQL salah. Update `.env`:
```env
DB_PASSWORD=your_password
```

### Error: "No connection could be made"
→ MySQL belum running. Start di Laragon.

### Warning: "Deprecated: Constant PDO::MYSQL_ATTR_SSL_CA"
→ Ini hanya warning PHP 8.5, tidak masalah. Abaikan saja.

### Error: "Class 'Filament\Panel' not found"
→ Filament belum terinstall. Jalankan:
```bash
composer require filament/filament:"^3.3" --ignore-platform-req=ext-intl
```

---

## 📁 Struktur Project

```
mcc-rekap-backend/
├── app/
│   ├── Filament/
│   │   ├── Resources/      # CRUD Resources
│   │   ├── Pages/          # Custom Pages
│   │   └── Widgets/        # Dashboard Widgets
│   ├── Models/
│   │   ├── Event.php       # ✅ Sudah ada
│   │   ├── MasterKategori.php  # ✅ Sudah ada
│   │   ├── MasterLokasi.php    # ✅ Sudah ada
│   │   ├── Organizer.php       # ✅ Sudah ada
│   │   └── ActivityLog.php     # ✅ Sudah ada
│   └── Providers/
│       └── Filament/
│           └── AdminPanelProvider.php  # ✅ Sudah ada
├── database/
│   ├── migrations/         # ✅ Sudah ada (6 migrations)
│   └── seeders/
│       ├── DatabaseSeeder.php      # ✅ Sudah ada
│       └── MasterDataSeeder.php    # ✅ Sudah ada
├── .env                    # ✅ Sudah dikonfigurasi (MySQL)
├── setup-db.bat           # ✅ Script setup otomatis
├── SETUP_DATABASE.md      # ✅ Panduan manual
└── FILAMENT_SETUP.md      # ✅ Panduan lengkap
```

---

## ✅ Checklist Setup

- [ ] Laragon running (Apache + MySQL hijau)
- [ ] Database `mcc_rekap` sudah dibuat
- [ ] Migration berhasil (8 tables)
- [ ] Seeder berhasil (user + master data)
- [ ] Filament terinstall
- [ ] Resources & Pages generated
- [ ] Development server running
- [ ] Bisa login ke `/admin`

---

## 🎯 Next Steps

Setelah setup berhasil:

1. **Customize Event Resource** - Form & table sesuai requirements
2. **Build Dashboard** - KPI cards, charts, alerts
3. **Implement Import** - Excel parser, 4-step wizard
4. **Implement Export** - Excel/CSV/JSON download
5. **Build Cek Duplikat** - Grouping & bulk actions
6. **Build Rekap Bulanan** - Metrics table & export
7. **Build Analitik Ekraf** - Charts & statistics
8. **Styling** - Sesuai design Vue app (Navy + Red)

---

**Status:** ✅ Database structure ready, ⏳ Waiting for Filament installation
