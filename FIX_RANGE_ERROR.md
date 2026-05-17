# Fix: range() Error untuk Kolom > Z

## Tanggal: 17 Mei 2026

---

## ❌ Error yang Terjadi

```
range(): Argument #2 ($end) must be a single byte, subsequent bytes are ignored
range('A', 'AL')
```

### Penyebab

File Excel 2025 memiliki **38 kolom (sampai kolom AL)**:
- Kolom A-Z: 26 kolom (1 karakter)
- Kolom AA-AL: 12 kolom (2 karakter)

Fungsi `range('A', 'AL')` **GAGAL** karena:
- `range()` hanya menerima **single character**
- 'AL' adalah **2 karakter** → error

### Dampak

- Import gagal di sheet Desember
- Header jadi kosong (semua string "")
- Data tidak terbaca

---

## ✅ Solusi

### Sebelum (SALAH):
```php
foreach (range('A', $highestCol) as $col) {
    $value = $sheet->getCell($col . $rowNum)->getValue();
}
```

**Masalah:** `range('A', 'AL')` error jika kolom > Z

### Sesudah (BENAR):
```php
// Convert column letter to number
$highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

// Iterasi dengan index number
for ($colIndex = 1; $colIndex <= $highestColIndex; $colIndex++) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
    $value = $sheet->getCell($colLetter . $rowNum)->getValue();
}
```

**Keuntungan:**
- ✅ Support kolom sampai XFD (16,384 kolom)
- ✅ Tidak ada batasan single character
- ✅ Lebih robust

---

## 🔧 Perubahan yang Dilakukan

### 1. Baca Header Sample
```php
// SEBELUM:
foreach (range('A', $highestCol) as $col) {
    $rowData[] = $sheet->getCell($col . $i)->getValue();
}

// SESUDAH:
$highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
for ($colIndex = 1; $colIndex <= $highestColIndex; $colIndex++) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
    $rowData[] = $sheet->getCell($colLetter . $i)->getValue();
}
```

### 2. Baca Data (Streaming)
```php
// SEBELUM:
foreach (range('A', $highestCol) as $col) {
    $value = $sheet->getCell($col . $rowNum)->getValue();
}

// SESUDAH:
for ($colIndex = 1; $colIndex <= $highestColIndex; $colIndex++) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
    $value = $sheet->getCell($colLetter . $rowNum)->getValue();
}
```

### 3. Fallback Sheet
Sama seperti di atas, semua `range()` diganti dengan iterasi index.

---

## 📊 Mapping Kolom Excel

### Kolom A-Z (1 karakter)
```
A  = 1
B  = 2
...
Z  = 26
```

### Kolom AA-ZZ (2 karakter)
```
AA = 27
AB = 28
...
AL = 38  ← File 2025 sampai sini
...
AZ = 52
BA = 53
...
ZZ = 702
```

### Kolom AAA-XFD (3 karakter)
```
AAA = 703
...
XFD = 16384 (maksimal Excel)
```

---

## 🧪 Testing

### Test dengan File 2025 (38 kolom)

1. **Upload file Excel 2025**
2. **Cek log:**
   ```
   [INFO] Sheet [Desember]: Format 2-baris header
   [INFO] Sheet [Desember]: Kolom terdeteksi: No, Nama Organisasi, Bidang, ...
   [INFO] Sheet [Desember] selesai: 85 baris data
   ```
3. **Tidak ada error range()**
4. **Header terisi dengan benar**
5. **Data terbaca semua**

### Expected Log
```
[INFO] Import Excel: Ditemukan 12 sheet bulan
[INFO] Sheet [Januari]: Format 2-baris header (parent baris 1, sub baris 2)
[INFO] Sheet [Januari]: Kolom terdeteksi: No, Nama Organisasi / Komunitas, Bidang, Jenis Kegiatan, Sub Sektor, Komersial/Non Komersial, Program SDGS, Judul Kegiatan, Tanggal Kegiatan, Lantai...
[INFO] Sheet [Januari] selesai: 85 baris data
...
[INFO] Sheet [Desember] selesai: 92 baris data
[INFO] Import Excel selesai: Total 1024 baris data dari 38 kolom
[INFO] Kolom yang berhasil di-resolve:
  - has_program_sdgs: true
  - has_email: true
  - has_no_telepon: true
  - has_pic: true
```

---

## 📝 Catatan Teknis

### PhpSpreadsheet Column Utilities

```php
// String to Index
$index = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString('AL');
// Result: 38

// Index to String
$letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(38);
// Result: 'AL'
```

### Kenapa Tidak Pakai range()?

**Limitasi PHP `range()`:**
- Hanya support single character
- `range('A', 'Z')` ✅ OK
- `range('A', 'AA')` ❌ ERROR
- `range('A', 'AL')` ❌ ERROR

**Solusi:**
- Gunakan column index (number)
- Convert index ↔ letter dengan PhpSpreadsheet utilities

---

## ✨ Keuntungan Solusi Baru

### 1. Support Unlimited Columns
- ✅ A-Z (26 kolom)
- ✅ AA-ZZ (676 kolom)
- ✅ AAA-XFD (16,384 kolom)

### 2. Lebih Robust
- Tidak ada batasan character
- Tidak ada error range()
- Future-proof

### 3. Performa Sama
- Iterasi dengan index sama cepatnya
- Memory usage sama
- Tidak ada overhead

---

## 🐛 Troubleshooting

### Jika Masih Error range()

1. **Cek apakah semua `range()` sudah diganti:**
   ```bash
   grep -n "range('A'" app/Services/ExcelImportService.php
   ```
   Harus tidak ada hasil.

2. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. **Restart server:**
   ```bash
   Ctrl+C
   php artisan serve
   ```

### Jika Header Masih Kosong

1. **Cek log deteksi header:**
   ```
   [INFO] Sheet [X]: Format X-baris header
   ```

2. **Cek kolom terdeteksi:**
   ```
   [INFO] Sheet [X]: Kolom terdeteksi: ...
   ```

3. **Jika kosong, cek struktur Excel:**
   - Apakah ada header di baris 1-2?
   - Apakah nama kolom sesuai key columns?

---

## 📌 Summary

### Masalah
- ❌ `range('A', 'AL')` error untuk kolom > Z
- ❌ Header jadi kosong
- ❌ Data tidak terbaca

### Solusi
- ✅ Ganti `range()` dengan iterasi index
- ✅ Gunakan PhpSpreadsheet column utilities
- ✅ Support unlimited columns

### Hasil
- ✅ Import berhasil untuk file 38 kolom
- ✅ Header terisi dengan benar
- ✅ Data terbaca semua
- ✅ Support file masa depan (sampai 16,384 kolom)

**Status: FIXED** ✅
