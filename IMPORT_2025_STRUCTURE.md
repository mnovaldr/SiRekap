# Struktur Import Data Tahun 2025

## Format Header Multi-Baris

### Struktur Excel 2025

```
Baris 1 (Parent Header):
┌─────┬──────────────────────┬─────────────────────────────────────────────────────────────┬─────────────────┬─────────────────┬────────┬──────────────────┬────────────────┬──────┬──────┬───────┐
│  B  │          C           │                         D - H                                │        I        │        J        │   K    │        L         │       M        │  N   │  O   │   P   │
├─────┼──────────────────────┼─────────────────────────────────────────────────────────────┼─────────────────┼─────────────────┼────────┼──────────────────┼────────────────┼──────┼──────┼───────┤
│ No  │ Nama Organisasi /    │              Kategori Pelaku (MERGED)                       │ Judul Kegiatan  │ Tanggal Kegiatan│ Lantai │ Lokasi / Ruangan │ Jumlah Peserta │ PIC  │ No.  │ Email │
│     │     Komunitas        │                                                             │                 │                 │        │                  │                │      │ Telf │       │
└─────┴──────────────────────┴─────────────────────────────────────────────────────────────┴─────────────────┴─────────────────┴────────┴──────────────────┴────────────────┴──────┴──────┴───────┘

Baris 2 (Sub-Header):
┌─────┬──────────────────────┬────────┬────────────────┬────────────┬──────────────────────┬──────────────┬─────────────────┬─────────────────┬────────┬──────────────────┬────────────────┬──────┬──────┬───────┐
│  B  │          C           │   D    │       E        │     F      │          G           │      H       │        I        │        J        │   K    │        L         │       M        │  N   │  O   │   P   │
├─────┼──────────────────────┼────────┼────────────────┼────────────┼──────────────────────┼──────────────┼─────────────────┼─────────────────┼────────┼──────────────────┼────────────────┼──────┼──────┼───────┤
│ No  │ Nama Organisasi /    │ Bidang │ Jenis Kegiatan │ Sub Sektor │ Komersial/Non        │ Program SDGS │ Judul Kegiatan  │ Tanggal Kegiatan│ Lantai │ Lokasi / Ruangan │ Jumlah Peserta │ PIC  │ No.  │ Email │
│     │     Komunitas        │        │                │            │ Komersial            │              │                 │                 │        │                  │                │      │ Telf │       │
└─────┴──────────────────────┴────────┴────────────────┴────────────┴──────────────────────┴──────────────┴─────────────────┴─────────────────┴────────┴──────────────────┴────────────────┴──────┴──────┴───────┘

Baris 3+: DATA
```

### Perbedaan dengan Tahun Sebelumnya

| Aspek | 2023 | 2024 | 2025 |
|-------|------|------|------|
| **Header Rows** | 1 baris | 1 baris | 2 baris (parent + sub) |
| **Kolom Baru** | - | Email, No. Telf, PIC | Program SDGS |
| **Kategori Pelaku** | Flat | Flat | Grouped (D-H merged) |
| **Jenis Kegiatan** | "Kegiatan" | "Jenis Kegiatan" | "Jenis Kegiatan" |

---

## Cara Kerja Sistem Import

### 1. Deteksi Header Multi-Baris

Fungsi `detectHeaderRow()` mendeteksi:
- **Baris 1**: Parent header (Kategori Pelaku merged)
- **Baris 2**: Sub-header (Bidang, Jenis Kegiatan, dll.)
- **Baris 3+**: Data

```php
[$headerRow, $dataStartRow, $parentRow] = $this->detectHeaderRow($headerSample);

// Hasil untuk 2025:
// $parentRow = 0 (baris 1 di Excel)
// $headerRow = 1 (baris 2 di Excel)
// $dataStartRow = 2 (baris 3 di Excel)
```

### 2. Merge Header Rows

Fungsi `mergeHeaderRows()` menggabungkan parent dan sub-header:

```php
// Input:
// Parent:  ['No', 'Nama Organisasi', 'Kategori Pelaku', '', '', '', '', 'Judul', ...]
// Sub:     ['No', 'Nama Organisasi', 'Bidang', 'Jenis', 'Sub', 'Komersial', 'SDGS', 'Judul', ...]

// Output (merged):
// ['No', 'Nama Organisasi', 'Bidang', 'Jenis Kegiatan', 'Sub Sektor', 'Komersial/Non Komersial', 'Program SDGS', 'Judul Kegiatan', ...]
```

**Aturan Merge:**
- Sub-header menimpa parent (prioritas lebih tinggi)
- Label grup ("Kategori Pelaku") dibuang
- Kolom kosong di sub menggunakan nilai dari parent

### 3. Resolve Kolom dengan Alias

Fungsi `resolveColumns()` memetakan nama kolom ke field database:

```php
'program_sdgs' => [
    'Program SDGS',      // 2025 exact
    'Program (SDGS)',    // variasi
    'Program SDGs',      // case variation
    'SDGS',
    'SDGs',
],
```

**Fallback:**
- Exact match dulu
- Case-insensitive match
- Null jika tidak ditemukan (aman untuk tahun lama)

---

## Mapping Kolom 2025

### Kolom Wajib

| Excel Column | Database Field | Alias |
|--------------|----------------|-------|
| No | - | (tidak disimpan) |
| Nama Organisasi / Komunitas | `nama_organisasi` | "Nama Organisasi", "Organisasi" |
| Bidang | `bidang` | "Bidang" |
| Jenis Kegiatan | `jenis_kegiatan` | "Jenis Kegiatan", "Kegiatan" |
| Sub Sektor | `sub_sektor` | "Sub Sektor", "Subsektor" |
| Komersial/Non Komersial | `komersial` | "Komersial/Non Komersial", "Komersial" |
| **Program SDGS** | `program_sdgs` | "Program SDGS", "SDGS", "SDGs" |
| Judul Kegiatan | `judul_kegiatan` | "Judul Kegiatan", "Event" |
| Tanggal Kegiatan | `tanggal` | "Tanggal Kegiatan", "Tanggal" |
| Lantai | `lantai` | "Lantai", "Lt" |
| Lokasi / Ruangan | `lokasi` | "Lokasi / Ruangan", "Lokasi" |
| Jumlah Peserta | `jumlah_peserta` | "Jumlah Peserta", "Peserta" |
| PIC | `pic` | "PIC", "Pic" |
| No. Telf | `no_telepon` | "No. Telf", "No. Telp", "Telepon" |
| Email | `email` | "Email", "E-mail" |

### Kolom Baru 2025

**Program SDGS** (Kolom H):
- Posisi: Setelah "Komersial/Non Komersial"
- Format: "SDGS 4", "SDGS 5; SDGS 10", "4, 5, 10"
- Nullable: Ya (NULL untuk data 2023/2024)
- Normalisasi: Otomatis ke format "SDGS X; SDGS Y"

---

## Proses Import Step-by-Step

### Step 1: Load Excel
```
1. Baca nama sheet → filter sheet bulan (Januari-Desember)
2. Load setiap sheet satu per satu (streaming)
```

### Step 2: Deteksi Header
```
1. Baca 10 baris pertama
2. Deteksi baris header (cari key columns)
3. Identifikasi parent row (jika ada)
4. Tentukan data start row
```

### Step 3: Merge Header (jika multi-baris)
```
1. Ambil parent row (baris 1)
2. Ambil sub-header row (baris 2)
3. Merge: sub menimpa parent
4. Buang label grup ("Kategori Pelaku")
```

### Step 4: Baca Data (Streaming)
```
1. Loop dari data start row sampai akhir
2. Baca cell by cell (hemat memory)
3. Map ke header yang sudah di-merge
4. Garbage collection setiap 100 baris
```

### Step 5: Standardize
```
1. Resolve kolom dengan alias mapping
2. Normalize nilai (Program SDGS, tanggal, dll.)
3. Split multi-lokasi/lantai
4. Clean data (trim, uppercase, dll.)
```

### Step 6: Detect Duplicates
```
1. Generate key_full dan key_short
2. Cek duplikat di batch dan database
3. Set status: Valid, Review, Duplikat
```

### Step 7: Bulk Insert
```
1. Chunk 300 baris per batch
2. Insert ke database
3. Log activity
```

---

## Logging untuk Debugging

### Log yang Dihasilkan

```
[INFO] Import Excel: Ditemukan 12 sheet bulan
[INFO] Sheet [Januari]: Format 2-baris header (parent baris 1, sub baris 2)
[INFO] Sheet [Januari]: Kolom terdeteksi: No, Nama Organisasi / Komunitas, Bidang, Jenis Kegiatan, Sub Sektor, Komersial/Non Komersial, Program SDGS, Judul Kegiatan, Tanggal Kegiatan, Lantai...
[INFO] Sheet [Januari]: Data mulai baris 3
[INFO] Sheet [Januari] selesai: 85 baris data
[INFO] Sheet [Januari] sample data:
  - nama_organisasi: "PT Example Indonesia"
  - bidang: "Bisnis"
  - judul: "Workshop Digital Marketing"
  - program_sdgs: "SDGS 4; SDGS 8"
[INFO] Import Excel selesai: Total 1024 baris data dari 15 kolom
[INFO] Kolom yang berhasil di-resolve dari Excel:
  - has_program_sdgs: true
  - has_email: true
  - has_no_telepon: true
  - has_pic: true
```

### Cek Log
```bash
tail -f storage/logs/laravel-*.log | grep "Sheet"
```

---

## Troubleshooting

### Data Tidak Terbaca

**Cek log untuk:**
1. Apakah header terdeteksi?
   ```
   [INFO] Sheet [Januari]: Format 2-baris header
   ```

2. Apakah kolom ter-resolve?
   ```
   [INFO] Kolom yang berhasil di-resolve: has_program_sdgs: true
   ```

3. Apakah data terbaca?
   ```
   [INFO] Sheet [Januari] selesai: 85 baris data
   ```

### Kolom Tidak Ter-resolve

**Solusi:**
1. Cek nama kolom di Excel (typo?)
2. Tambahkan alias baru di `resolveColumns()`
3. Cek case sensitivity

### Program SDGS Tidak Terisi

**Cek:**
1. Apakah kolom ada di Excel?
2. Apakah nama kolom sesuai alias?
3. Cek log: `has_program_sdgs: true/false`

---

## Testing Checklist

### Test dengan File 2025

- [ ] Upload file Excel 2025
- [ ] Cek log: "Format 2-baris header"
- [ ] Cek log: "has_program_sdgs: true"
- [ ] Cek preview: Program SDGS terisi
- [ ] Cek preview: Email, No. Telf, PIC terisi
- [ ] Import berhasil tanpa error
- [ ] Cek database: program_sdgs terisi

### Test dengan File 2024

- [ ] Upload file Excel 2024
- [ ] Cek log: "Format 1-baris header"
- [ ] Cek log: "has_program_sdgs: false"
- [ ] Cek preview: Program SDGS = NULL
- [ ] Cek preview: Email, No. Telf, PIC terisi
- [ ] Import berhasil tanpa error

### Test dengan File 2023

- [ ] Upload file Excel 2023
- [ ] Cek log: "Format 1-baris header"
- [ ] Cek log: "has_program_sdgs: false"
- [ ] Cek preview: Program SDGS = NULL
- [ ] Cek preview: Email, No. Telf, PIC = NULL
- [ ] Import berhasil tanpa error

---

## Summary

### Fitur yang Mendukung Format 2025

✅ **Multi-row header detection** - Otomatis deteksi 2 baris header
✅ **Header merging** - Gabungkan parent + sub-header
✅ **Alias mapping** - Tangani variasi nama kolom
✅ **Nullable fields** - Kolom baru aman untuk file lama
✅ **Streaming mode** - Hemat memory untuk file besar
✅ **Comprehensive logging** - Debug info lengkap

### Backward Compatibility

✅ **2023** - 1 baris header, tanpa Email/PIC/SDGS
✅ **2024** - 1 baris header, dengan Email/PIC, tanpa SDGS
✅ **2025** - 2 baris header, dengan Email/PIC/SDGS

**Status: READY FOR 2025 DATA** ✅
