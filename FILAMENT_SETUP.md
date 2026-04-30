# Setup Laravel Filament untuk MCC REKAP

## Status Saat Ini

✅ **Sudah Selesai:**
1. Migrations updated (events, master_lokasi, master_kategori, organizers, activity_logs)
2. Models created (Event, MasterKategori, MasterLokasi, Organizer, ActivityLog)
3. Seeders created (MasterDataSeeder dengan data master)
4. Filament AdminPanelProvider created
5. Provider registered

⚠️ **Belum Selesai:**
- Filament package belum terinstall sempurna (issue dengan PHP intl extension)
- Migrations belum dijalankan
- Filament Resources belum dibuat

---

## Langkah Instalasi

### 1. Fix PHP Extensions (PENTING!)

Filament membutuhkan `intl` dan `exif` extensions. Anda perlu enable di php.ini:

**Lokasi php.ini:** `C:\laragon\bin\php\php-8.5.4-nts-Win32-vs17-x64\php.ini`

**Cara:**
1. Buka php.ini dengan text editor (as Administrator)
2. Cari baris:
   ```ini
   extension=exif
   extension=intl
   ```
3. Pastikan TIDAK ada `;` di depannya (sudah uncommented)
4. Jika masih error "Application Control policy blocked", coba:
   - Disable antivirus sementara
   - Atau copy `php_intl.dll` dan `php_exif.dll` dari folder `ext/` ke folder root PHP
5. Restart Laragon/Apache

**Verifikasi:**
```bash
php -m | grep intl
php -m | grep exif
```

### 2. Install Filament (Setelah Extensions Fixed)

```bash
cd mcc-rekap-backend
composer require filament/filament:"^3.3" --with-all-dependencies
```

### 3. Jalankan Migrations

```bash
php artisan migrate:fresh --seed
```

Ini akan:
- Create semua tables (users, events, master_lokasi, master_kategori, organizers, activity_logs)
- Seed user admin (email: admin@mcc.com, password: password)
- Seed master data (Bidang, Jenis Kegiatan, Lokasi)

### 4. Buat Filament Resources

```bash
php artisan make:filament-resource Event --generate
php artisan make:filament-resource MasterKategori --generate
php artisan make:filament-resource MasterLokasi --generate
php artisan make:filament-resource Organizer --generate
php artisan make:filament-resource ActivityLog --generate --view
```

### 5. Buat Custom Pages

```bash
php artisan make:filament-page ImportData
php artisan make:filament-page ExportData
php artisan make:filament-page CekDuplikat
php artisan make:filament-page RekapBulanan
php artisan make:filament-page AnalitikEkraf
php artisan make:filament-page RiwayatOperasi
```

### 6. Buat Widgets untuk Dashboard

```bash
php artisan make:filament-widget StatsOverview --stats
php artisan make:filament-widget EventsChart --chart
php artisan make:filament-widget TopJenisKegiatan
php artisan make:filament-widget TopLokasi
php artisan make:filament-widget RecentReviewEvents
```

### 7. Jalankan Development Server

```bash
php artisan serve
```

Akses: `http://localhost:8000/admin`
Login: `admin@mcc.com` / `password`

---

## Fitur yang Akan Dibangun

### ✅ Sudah Ada di Backend:
1. **Event Model** dengan:
   - Auto-generate bulan, key_full, key_short
   - Duplicate detection (FULL dan SHORT)
   - Scopes: valid(), review(), duplikat(), byYear(), byMonth()

2. **Master Data Models**:
   - MasterKategori (bidang, jenis_kegiatan)
   - MasterLokasi
   - Organizer

3. **Activity Log** untuk audit trail

### 🔨 Yang Perlu Dibangun di Filament:

#### 1. Dashboard
- KPI Cards: Total Event, Total Peserta, Ekraf Unik, % Non Komersial
- Monthly Trend Chart (Bar chart)
- Top Jenis Kegiatan (Top 5)
- Distribusi Bidang
- Top Lokasi (Top 5)
- Alert untuk Duplikat/Review
- Recent Review Events table

#### 2. Event Resource (CRUD)
- Form dengan 13 fields
- Table dengan filters: Status, Search, Year, Month
- Inline edit
- Actions: Validate, Delete
- Bulk actions
- Status badges (Valid/Review/Duplikat)

#### 3. Import Data Page
- 4-step wizard:
  1. Upload Excel (.xlsx/.xls)
  2. Column mapping (auto-mapped)
  3. Validation & Preview
  4. Confirm & Save
- Excel parser (multi-sheet support)
- Duplicate detection
- Statistics: Total, Valid, Duplikat, Review

#### 4. Export Data Page
- Format selection: Excel, CSV, JSON
- Date range filter (Year, Month)
- Preview before export
- Download with proper filename

#### 5. Cek Duplikat Page
- Group duplicates by type (FULL, SHORT)
- Show duplicate groups
- Actions: Delete, Validate, Process Group
- Summary KPIs

#### 6. Rekap Bulanan Page
- Two modes: Single Year, Year Range
- Metrics table: Total Event, Total Peserta, Ekraf Unik, Komersial, Non Komersial
- Export to Excel
- Pelaku Ekraf Unik calculation (global unique per period)

#### 7. Analitik Ekraf Page
- Sub Sektor analysis (Top 5)
- Room utility (Top 5)
- Percentage bars

#### 8. Master Data Resource
- Tabs: Bidang, Jenis Kegiatan, Lokasi
- Mapping table: Raw → Standard
- Frequency count
- Add/Edit/Delete mappings

#### 9. Riwayat Operasi Page
- Activity log table
- Filter by action type
- Statistics chart
- Export logs

---

## Database Schema

### events
- id (UUID)
- nama_organisasi, bidang, jenis_kegiatan, sub_sektor, komersial
- judul_kegiatan, tanggal, bulan
- lantai, lokasi, jumlah_peserta
- email, no_telepon, pic
- status_data (Valid/Review/Duplikat), catatan_review
- key_full, key_short (auto-generated)
- timestamps

### master_kategori
- id, type (bidang/jenis_kegiatan/komunitas)
- raw_value, standard_value, frequency
- timestamps

### master_lokasi
- id, raw_value, standard_value, frequency
- timestamps

### organizers
- id, nama, bidang, email, no_telepon, total_events
- timestamps

### activity_logs
- id, action, model_type, model_id, user_id
- description, properties (JSON)
- timestamps

---

## Enums/Options

### Bidang (7 options)
- Komunitas, Bisnis, Akademisi, Pemerintah, Media, Perorangan, Institusi Finansial

### Jenis Kegiatan (18 options)
- Pertunjukan, Meeting, Workshop, Pelatihan, Talkshow/Podcast, Take Konten Foto & Video, Produksi Musik, Pameran, Kunjungan, Sosialisasi, Kompetisi, Produksi Film, Voice Over, Interview, Penelitian, Produksi Olahan, Konsultasi, Lainnya

### Lokasi (14 options)
- Studio Musik, Studio Foto, Co-Working Space 1, Co-Working Space 2, Food Lab, Multi Purpose Area, Amphitheater 1, Amphitheater 2, Multifunction Room, Open Public Space Barat, Open Public Space Utara, Ruang Meeting, Ruang Fashion, KIDZONE

### Lantai (4 options)
- 2, 3, 4, 5

### Komersial (2 options)
- Komersial, Non Komersial

### Status Data (3 options)
- Valid, Review, Duplikat

---

## Next Steps

Setelah Filament terinstall, saya akan:
1. Customize Event Resource dengan form & table sesuai requirements
2. Build Dashboard dengan widgets
3. Implement Import/Export pages dengan Excel handling
4. Build Cek Duplikat page dengan grouping logic
5. Build Rekap Bulanan dengan calculation logic
6. Build Analitik Ekraf page
7. Build Riwayat Operasi dengan activity log
8. Styling sesuai design Vue app (Navy #0f1f45, Red #e63946)

---

## Catatan Penting

1. **Duplicate Detection Logic:**
   - KEY FULL = org + title + date + room + floor → DUPLIKAT
   - KEY SHORT = org + title + date (different room) → REVIEW

2. **Pelaku Ekraf Unik:**
   - Per period: Count unique nama_organisasi in that period
   - NOT sum of monthly counts (1 org = 1 count per year)

3. **Multi-Lantai/Multi-Lokasi:**
   - Excel parser akan split "2,3" menjadi 2 rows
   - Cartesian product untuk kombinasi lantai × lokasi

4. **Activity Log:**
   - Log semua operations: import, export, create, update, delete, validate
   - Store before/after values in properties JSON

5. **Excel Import:**
   - Support multi-sheet (Januari-Desember)
   - Auto-standardize data
   - Detect duplicates before import
   - Only import Valid + Review (exclude Duplikat)

---

**Status:** Ready untuk install Filament setelah PHP extensions fixed.
