# Panduan Import Data Excel

## Perbedaan Format Antar Tahun

Sistem ini dirancang untuk menangani perbedaan format Excel antar tahun secara otomatis.

### Format Tahun 2023
**Kolom yang ada:**
- No
- Nama Organisasi / Komunitas
- Bidang
- Kegiatan (bukan "Jenis Kegiatan")
- Sub Sektor
- Komersial/Non Komersial
- Judul Kegiatan
- Tanggal Kegiatan
- Lantai
- Lokasi / Ruangan
- Jumlah Peserta

**Kolom yang TIDAK ADA:**
- ❌ Email
- ❌ No. Telf
- ❌ PIC
- ❌ Program SDGS

### Format Tahun 2024
**Kolom yang ada:**
- No
- Nama Organisasi / Komunitas
- Bidang
- Jenis Kegiatan (bukan "Kegiatan")
- Sub Sektor
- Komersial/Non Komersial
- Judul Kegiatan
- Tanggal Kegiatan
- Lantai
- Lokasi / Ruangan
- Jumlah Peserta
- ✅ Email
- ✅ No. Telf
- ✅ PIC

**Kolom yang TIDAK ADA:**
- ❌ Program SDGS

### Format Tahun 2025
**Kolom yang ada:**
- No
- Nama Organisasi / Komunitas
- Bidang
- Jenis Kegiatan
- Sub Sektor
- Komersial/Non Komersial
- ✅ **Program SDGS** (KOLOM BARU setelah Komersial)
- Judul Kegiatan
- Tanggal Kegiatan
- Lantai
- Lokasi / Ruangan
- Jumlah Peserta
- PIC
- No. Telf
- Email

## Cara Kerja Sistem

### 1. Alias Mapping (resolveColumns)
Sistem menggunakan alias mapping untuk menangani perbedaan nama kolom:

```php
'jenis_kegiatan' => [
    'Jenis Kegiatan',  // 2024, 2025
    'Kegiatan',        // 2023
],
```

### 2. Nullable Fields
Kolom yang tidak ada di file lama otomatis diisi `NULL`:

```php
'program_sdgs' => [
    'Program SDGS',   // 2025+
    'SDGS',
    'SDGs',
    'Program SDGs',
],
```

Jika kolom tidak ditemukan di Excel 2023/2024, nilai akan `NULL` tanpa error.

### 3. Memory Management
Untuk file besar (2025: 12 sheet):
- Memory limit dinaikkan ke 2048M
- Timeout dinaikkan ke 10 menit
- Garbage collection setelah setiap sheet
- Chunk reading untuk efisiensi

## Troubleshooting

### Error 500 saat Import
**Penyebab:** Memory exhausted atau timeout

**Solusi:**
1. Pastikan `php.ini` memiliki `memory_limit = 2048M`
2. Pastikan `max_execution_time = 600` (10 menit)
3. Cek log di `storage/logs/laravel.log`

### Kolom Tidak Terbaca
**Penyebab:** Nama kolom tidak sesuai alias

**Solusi:**
1. Cek log untuk melihat kolom yang berhasil di-resolve
2. Tambahkan alias baru di `resolveColumns()` jika perlu
3. Pastikan header Excel tidak ada typo

### Data Duplikat
**Penyebab:** Data sudah ada di database

**Solusi:**
1. Sistem otomatis mendeteksi duplikat menggunakan key_full dan key_short
2. Data duplikat ditandai dengan status "Duplikat" atau "Review"
3. Hanya data "Valid" yang akan diimport

## Menambahkan Kolom Baru di Tahun Berikutnya

Jika ada kolom baru di tahun 2026+:

1. **Tambahkan kolom di database migration:**
```php
$table->string('kolom_baru')->nullable();
```

2. **Tambahkan di model Event ($fillable):**
```php
protected $fillable = [
    // ... kolom lain
    'kolom_baru',
];
```

3. **Tambahkan alias di ExcelImportService::resolveColumns():**
```php
'kolom_baru' => [
    'Kolom Baru',
    'Nama Alternatif Kolom',
],
```

4. **Tambahkan di ImportData::confirmImport() $allowedColumns:**
```php
$allowedColumns = [
    // ... kolom lain
    'kolom_baru',
];
```

5. **Selesai!** Sistem akan otomatis menangani file lama (kolom diisi NULL) dan file baru (kolom diisi dari Excel).

## Logging

Sistem mencatat proses import di log:
- Sheet yang diproses
- Kolom yang berhasil di-resolve
- Error yang terjadi
- Total data yang diimport

Cek log di: `storage/logs/laravel.log`

## Performa

**File 2025 (12 sheet, ~1000 baris):**
- Memory usage: ~1.5GB
- Waktu proses: ~3-5 menit
- Chunk size: 300 baris per batch insert

**Optimasi:**
- Bulk insert menggunakan `Event::insert()` (bukan `create()`)
- Batch 300 baris sekaligus
- Garbage collection setelah setiap sheet
- Read-only mode untuk PhpSpreadsheet
