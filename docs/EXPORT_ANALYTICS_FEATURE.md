# 📊 Fitur Baru: Export Laporan Analitik

## 🎯 Overview

Fitur export data telah ditingkatkan dengan kemampuan baru:
1. **Rentang Periode Tahunan** - Export data dari tahun ke tahun (misal: 2022-2024)
2. **Export Laporan Analitik** - Export ringkasan analitik seperti halaman Analitik Ekraf
3. **Dua Tipe Export** - Detail (per event) dan Summary (analitik)

## ✨ Fitur Baru

### 1. Rentang Periode Tahunan

**Sebelumnya:**
- Hanya bisa pilih 1 tahun
- Tidak bisa export data multi-tahun

**Sekarang:**
- ✅ Pilih tahun awal dan tahun akhir
- ✅ Export data dari rentang tahun (misal: 2022-2024)
- ✅ Otomatis mendeteksi tahun terlama dan terbaru
- ✅ Validasi: tahun awal tidak boleh lebih besar dari tahun akhir

**Contoh Penggunaan:**
```
Tahun Awal: 2022
Tahun Akhir: 2024
→ Export semua data dari 2022 sampai 2024
```

### 2. Tipe Export: Detail vs Summary

#### A. **Data Detail** (Default)
Export semua data event per baris seperti sebelumnya.

**Kolom yang diekspor:**
- Organisasi
- Bidang
- Jenis Kegiatan
- Sub Sektor
- Komersial
- Judul Kegiatan
- Tanggal
- Bulan
- Lantai
- Lokasi
- Jumlah Peserta
- Email
- No Telepon
- PIC
- Status

**Format tersedia:** Excel, CSV, JSON, PDF

#### B. **Laporan Analitik** (Baru! 🆕)
Export ringkasan analitik seperti halaman Analitik Ekraf.

**Data yang diekspor:**
1. **Ringkasan Statistik**
   - Total Event
   - Total Pengunjung
   - Total Pelaku Unik
   - Komersial vs Non Komersial

2. **Distribusi Sub Sektor**
   - Daftar sub sektor dengan jumlah event
   - Persentase per sub sektor
   - Top 10 sub sektor (untuk PDF)

3. **Jenis Kegiatan**
   - Daftar jenis kegiatan dengan jumlah
   - Persentase per jenis
   - Top 10 jenis kegiatan (untuk PDF)

4. **Bidang Pelaku**
   - Daftar bidang dengan jumlah event
   - Persentase per bidang

5. **Rekapitulasi Bulanan**
   - Per tahun dalam rentang periode
   - Total event per bulan
   - Total pengunjung per bulan
   - Komersial vs Non Komersial per bulan
   - Pelaku unik per bulan

**Format tersedia:** Excel (multi-sheet), CSV, JSON, PDF

## 📋 Cara Menggunakan

### Export Data Detail

1. **Pilih Tipe Export:** "Data Detail"
2. **Pilih Rentang Periode:**
   - Tahun Awal: 2024
   - Tahun Akhir: 2024 (untuk 1 tahun)
   - Atau: 2022 - 2024 (untuk multi tahun)
3. **Pilih Filter Bulan:** (Opsional)
   - "Semua" untuk semua bulan
   - Atau pilih bulan spesifik
4. **Pilih Format:** Excel / CSV / JSON / PDF
5. **Klik "Generate Download"**

### Export Laporan Analitik

1. **Pilih Tipe Export:** "Laporan Analitik"
2. **Pilih Rentang Periode:**
   - Tahun Awal: 2022
   - Tahun Akhir: 2024
3. **Pilih Format:** Excel / CSV / JSON / PDF
4. **Klik "Generate Download"**

**Note:** Filter bulan tidak tersedia untuk laporan analitik (selalu semua bulan).

## 📊 Format Export Laporan Analitik

### Excel (.xlsx)
**5 Sheet:**
1. **Ringkasan** - Statistik utama
2. **Sub Sektor** - Tabel distribusi sub sektor
3. **Jenis Kegiatan** - Tabel jenis kegiatan
4. **Bidang Pelaku** - Tabel bidang pelaku
5. **Rekap Bulanan** - Tabel rekap per bulan per tahun

**Fitur:**
- Header berwarna ungu
- Format angka dengan pemisah ribuan
- Persentase otomatis
- Lebar kolom optimal

### PDF (.pdf)
**Konten:**
- Header dengan logo MCC
- Summary box dengan 3 metrik utama
- Tabel distribusi (top 10 untuk setiap kategori)
- Rekap bulanan per tahun
- Footer dengan timestamp

**Fitur:**
- Portrait A4
- Professional layout
- Color-coded sections
- Page breaks untuk readability

### CSV (.csv)
**Struktur:**
```csv
RINGKASAN ANALITIK
Metrik,Nilai
Total Event,1234
Total Pengunjung,56789

SUB SEKTOR
No,Kategori,Jumlah
1,Musik,450
2,Seni Rupa,320
...
```

### JSON (.json)
**Struktur:**
```json
{
  "metadata": {
    "title": "Laporan Analitik Ekonomi Kreatif MCC",
    "exported_at": "2024-05-02T14:30:00+07:00",
    "period_start": 2022,
    "period_end": 2024
  },
  "summary": {
    "total_events": 1234,
    "total_visitors": 56789,
    "total_organizers": 456
  },
  "sub_sektor": [...],
  "jenis_kegiatan": [...],
  "bidang_pelaku": [...],
  "monthly_recap": {
    "2022": [...],
    "2023": [...],
    "2024": [...]
  }
}
```

## 🎨 UI/UX Improvements

### 1. Tipe Export Selector
- Radio button dengan icon
- Visual feedback (border biru saat dipilih)
- Deskripsi singkat untuk setiap tipe

### 2. Rentang Periode
- Dua dropdown side-by-side
- Separator "—" di tengah
- Label dinamis menampilkan periode yang dipilih
- Contoh: "📅 Periode 2022 - 2024"

### 3. Preview Panel
**Untuk Data Detail:**
- Tabel preview seperti sebelumnya
- Counter: "9,233 records tersedia"
- Status indicator: hijau (data ready) / merah (no data)

**Untuk Laporan Analitik:**
- Icon chart bar
- Deskripsi konten laporan
- Checklist fitur yang akan diekspor
- Tidak ada preview tabel (karena summary)

### 4. Empty State
- Icon warning (kuning)
- Pesan spesifik menyebutkan periode
- Tips box dengan saran

## 🔧 Technical Details

### Backend Changes

**File Modified:** `app/Filament/Pages/ExportData.php`

**New Properties:**
```php
public string $selectedYearStart;  // Tahun awal
public string $selectedYearEnd;    // Tahun akhir
public string $exportType = 'detail'; // 'detail' or 'summary'
```

**New Methods:**
```php
getAnalyticsData()           // Get data analitik
exportSummaryXlsx()          // Export summary ke Excel
exportSummaryPdf()           // Export summary ke PDF
exportSummaryCsv()           // Export summary ke CSV
exportSummaryJson()          // Export summary ke JSON
createSummaryXlsxSpreadsheet() // Create Excel multi-sheet
addSummaryOverviewSheet()    // Add ringkasan sheet
addCategorySheet()           // Add kategori sheet
addMonthlyRecapSheet()       // Add rekap bulanan sheet
```

**Modified Methods:**
```php
mount()                      // Auto-detect year range
getDateRange()               // Support year range
refreshCount()               // Handle summary type
export()                     // Route to detail/summary
generateFilename()           // Include type in filename
```

### Frontend Changes

**File Modified:** `resources/views/filament/pages/export-data.blade.php`

**New UI Elements:**
- Export type selector (Detail / Summary)
- Year range picker (Start - End)
- Conditional month picker (only for detail)
- Dynamic preview panel
- Summary preview with checklist

**New Template:** `resources/views/exports/analytics-pdf.blade.php`

## 📝 Naming Convention

### Filename Format

**Data Detail:**
```
MCC_Export_Detail_[Periode]_[Timestamp].[ext]
```

**Laporan Analitik:**
```
MCC_Export_Analitik_[Periode]_[Timestamp].[ext]
```

**Contoh:**
- `MCC_Export_Detail_Tahun_2024_20240502_143022.xlsx`
- `MCC_Export_Detail_2022-2024_20240502_143022.pdf`
- `MCC_Export_Analitik_2022-2024_20240502_143022.xlsx`
- `MCC_Export_Analitik_Januari_2024_20240502_143022.pdf`

## 🧪 Testing Checklist

### Test Case 1: Export Detail Single Year
- [ ] Pilih Detail, Tahun 2024-2024, Semua Bulan
- [ ] Export Excel → File terdownload dengan data 2024
- [ ] Export PDF → File terdownload dengan data 2024

### Test Case 2: Export Detail Multi Year
- [ ] Pilih Detail, Tahun 2022-2024, Semua Bulan
- [ ] Export Excel → File berisi data 2022, 2023, 2024
- [ ] Verify jumlah records sesuai

### Test Case 3: Export Detail dengan Filter Bulan
- [ ] Pilih Detail, Tahun 2024-2024, Bulan Januari
- [ ] Export → Hanya data Januari 2024

### Test Case 4: Export Analitik Single Year
- [ ] Pilih Analitik, Tahun 2024-2024
- [ ] Export Excel → 5 sheets (Ringkasan, Sub Sektor, dll)
- [ ] Verify data sesuai dengan Analitik Ekraf

### Test Case 5: Export Analitik Multi Year
- [ ] Pilih Analitik, Tahun 2022-2024
- [ ] Export Excel → Rekap Bulanan ada 3 tahun
- [ ] Export PDF → Data agregat 3 tahun

### Test Case 6: Year Range Validation
- [ ] Set Tahun Awal: 2024, Tahun Akhir: 2022
- [ ] System auto-correct → Tahun Akhir jadi 2024

### Test Case 7: Preview Panel
- [ ] Pilih Detail → Preview tabel muncul
- [ ] Pilih Analitik → Preview checklist muncul
- [ ] Counter update saat ganti periode

### Test Case 8: All Formats
- [ ] Export Analitik ke Excel → Multi-sheet
- [ ] Export Analitik ke PDF → Professional layout
- [ ] Export Analitik ke CSV → Structured data
- [ ] Export Analitik ke JSON → Valid JSON

## 📊 Use Cases

### Use Case 1: Laporan Tahunan
**Scenario:** Buat laporan tahunan 2024
- Pilih: Analitik, 2024-2024, PDF
- Result: Laporan lengkap dengan chart dan tabel

### Use Case 2: Laporan Multi-Tahun
**Scenario:** Analisis tren 3 tahun terakhir
- Pilih: Analitik, 2022-2024, Excel
- Result: Excel dengan rekap per tahun

### Use Case 3: Export Data Mentah
**Scenario:** Backup data untuk analisis eksternal
- Pilih: Detail, 2022-2024, CSV
- Result: CSV dengan semua event

### Use Case 4: Presentasi Stakeholder
**Scenario:** Presentasi ke Dinas Kebudayaan
- Pilih: Analitik, 2024-2024, PDF
- Result: PDF professional siap print

### Use Case 5: Integrasi API
**Scenario:** Kirim data ke sistem lain
- Pilih: Analitik, 2024-2024, JSON
- Result: JSON terstruktur untuk API

## 🚀 Benefits

### Untuk User:
- ✅ Lebih fleksibel dalam memilih periode
- ✅ Laporan analitik siap pakai (tidak perlu olah manual)
- ✅ Format Excel multi-sheet lebih terorganisir
- ✅ PDF professional untuk presentasi
- ✅ Hemat waktu (tidak perlu copy-paste dari Analitik Ekraf)

### Untuk Management:
- ✅ Laporan tahunan otomatis
- ✅ Analisis tren multi-tahun
- ✅ Data terstruktur untuk decision making
- ✅ Professional output untuk stakeholder

### Untuk Developer:
- ✅ JSON API-ready
- ✅ Struktur data konsisten
- ✅ Metadata lengkap
- ✅ Easy integration

## 📞 Support

### Troubleshooting

**Q: Export analitik tidak ada data**
A: Pastikan ada data Valid di rentang tahun yang dipilih

**Q: Excel multi-sheet tidak muncul**
A: Pastikan pilih tipe "Laporan Analitik", bukan "Data Detail"

**Q: Rekap bulanan kosong**
A: Cek apakah field `bulan` terisi di database

**Q: PDF terlalu panjang**
A: Untuk data banyak, gunakan Excel atau batasi rentang tahun

### Contact

- 📧 Email: support@mcc.go.id
- 📱 WhatsApp: +62 xxx xxxx xxxx
- 🌐 Website: https://mcc.go.id

---

**Developed by:** Kiro AI Assistant  
**Date:** 2 Mei 2026  
**Version:** 3.0  
**Feature:** Export Laporan Analitik dengan Rentang Periode Tahunan
