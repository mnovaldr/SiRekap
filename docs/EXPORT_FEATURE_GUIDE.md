# 📊 Panduan Fitur Export Data MCC

## Deskripsi
Fitur Export Data memungkinkan Anda mengekspor data event MCC dalam berbagai format untuk keperluan laporan, analisis, dan integrasi sistem.

## Format Export yang Tersedia

### 1. 📊 Excel (.xlsx)
**Fitur:**
- Template profesional dengan logo MCC
- Header yang terformat dengan baik
- Warna baris bergantian untuk kemudahan membaca
- Auto-filter pada header
- Freeze panes untuk navigasi mudah
- Border dan styling yang rapi
- Summary row dengan total records
- Siap untuk dicetak (landscape A4)

**Cocok untuk:**
- Laporan formal
- Analisis data dengan Excel
- Presentasi ke stakeholder
- Arsip dokumentasi

### 2. 📄 CSV (.csv)
**Fitur:**
- Format data mentah dalam teks
- UTF-8 dengan BOM (kompatibel dengan Excel)
- Header dengan label yang jelas
- Format tanggal: dd/mm/yyyy
- Format angka dengan pemisah ribuan

**Cocok untuk:**
- Import ke sistem lain
- Analisis dengan tools data science
- Backup data sederhana
- Integrasi dengan database

### 3. 🔷 JSON (.json)
**Fitur:**
- Struktur data terorganisir
- Metadata lengkap (exported_at, period, total_records)
- Column labels untuk referensi
- Summary statistics (total events, total participants)
- Pretty print untuk readability

**Cocok untuk:**
- API integration
- Web applications
- Data processing dengan JavaScript/Python
- Backup terstruktur

### 4. 📕 PDF (.pdf)
**Fitur:**
- Dokumen siap cetak (landscape A4)
- Header profesional dengan logo
- Info box dengan ringkasan
- Tabel dengan styling modern
- Status badge berwarna (Valid/Review/Duplikat)
- Footer dengan timestamp
- Summary box di akhir dokumen

**Cocok untuk:**
- Laporan cetak
- Dokumentasi resmi
- Presentasi
- Arsip permanen

## Cara Menggunakan

### 1. Akses Halaman Export
- Login ke sistem SiREKAP MCC
- Navigasi ke menu **Import / Export** → **Export Data**

### 2. Konfigurasi Export
**a. Pilih Tahun Anggaran**
- Dropdown akan menampilkan tahun-tahun yang tersedia di database
- Default: tahun berjalan

**b. Pilih Periode Bulan**
- "Semua Bulan" untuk export data setahun penuh
- Atau pilih bulan spesifik (Januari - Desember)

**c. Pilih Format Output**
- Klik salah satu format yang diinginkan
- Setiap format memiliki deskripsi singkat

### 3. Preview Data
- Panel kanan menampilkan preview data yang akan diekspor
- Menampilkan 10 data pertama
- Gunakan pagination untuk melihat lebih banyak

### 4. Generate Download
- Klik tombol **"Generate Download"**
- Tunggu proses export selesai
- File akan otomatis terdownload

## Kolom Data yang Diekspor

| Kolom | Deskripsi |
|-------|-----------|
| Organisasi | Nama organisasi penyelenggara |
| Bidang | Bidang kegiatan |
| Jenis Kegiatan | Tipe/kategori kegiatan |
| Sub Sektor | Sub sektor ekonomi kreatif |
| Komersial | Status komersial (Ya/Tidak) |
| Judul Kegiatan | Nama lengkap kegiatan |
| Tanggal | Tanggal pelaksanaan |
| Bulan | Bulan dalam bahasa Indonesia |
| Lantai | Lokasi lantai di MCC |
| Lokasi | Ruangan/area spesifik |
| Peserta | Jumlah peserta |
| Email | Email kontak |
| No Telepon | Nomor telepon kontak |
| PIC | Person in Charge |
| Status | Status data (Valid/Review/Duplikat) |

## Penamaan File

Format nama file yang dihasilkan:
```
MCC_Export_{Periode}_{Tahun}_{Timestamp}.{format}
```

**Contoh:**
- `MCC_Export_Tahun_2024_20240502_143022.xlsx`
- `MCC_Export_Januari_2024_20240502_143022.pdf`
- `MCC_Export_Semua_Bulan_(Januari_-_Desember)_2024_20240502_143022.csv`

## Tips & Best Practices

### 📊 Excel (XLSX)
- Gunakan freeze panes untuk scroll data besar
- Manfaatkan auto-filter untuk sorting dan filtering
- Kolom sudah diatur lebar optimal, tapi bisa disesuaikan
- Cocok untuk membuat pivot table dan chart

### 📄 CSV
- Buka dengan Excel atau text editor
- Pastikan encoding UTF-8 saat import ke sistem lain
- Ideal untuk processing dengan Python pandas atau R

### 🔷 JSON
- Parse dengan `JSON.parse()` di JavaScript
- Gunakan `json.loads()` di Python
- Metadata berguna untuk tracking dan audit
- Summary statistics bisa langsung digunakan

### 📕 PDF
- Set printer ke landscape untuk hasil optimal
- Gunakan "Fit to page" saat print
- Status berwarna memudahkan identifikasi
- Cocok untuk lampiran email atau presentasi

## Troubleshooting

### Export Gagal
**Masalah:** Notifikasi "Export Gagal" muncul
**Solusi:**
1. Cek koneksi internet (untuk logo dan assets)
2. Pastikan ada data untuk periode yang dipilih
3. Coba format export lain
4. Hubungi administrator jika masalah berlanjut

### File Tidak Terdownload
**Masalah:** Tombol diklik tapi file tidak download
**Solusi:**
1. Cek popup blocker browser
2. Cek permission download di browser
3. Coba browser lain (Chrome, Firefox, Edge)
4. Clear cache browser

### Data Tidak Sesuai
**Masalah:** Data di export berbeda dengan di sistem
**Solusi:**
1. Refresh halaman export
2. Cek filter tahun dan bulan
3. Pastikan data sudah tersimpan di database
4. Hanya data dengan status "Valid" yang diekspor

### PDF Tidak Rapi
**Masalah:** Layout PDF berantakan
**Solusi:**
1. Pastikan logo MCC ada di `public/images/logo.png`
2. Gunakan viewer PDF terbaru (Adobe Reader, Chrome)
3. Coba export ulang
4. Jika data terlalu banyak, export per bulan

## Keamanan & Privacy

- ✅ Hanya user yang login bisa mengakses fitur export
- ✅ Data yang diekspor sesuai dengan permission user
- ✅ Semua aktivitas export tercatat di log sistem
- ✅ File tidak disimpan di server (langsung download)
- ⚠️ Jangan share file export ke pihak tidak berwenang
- ⚠️ Hapus file export dari komputer setelah tidak digunakan

## Batasan & Limitasi

- **Maksimal data:** Tidak ada batasan, tapi untuk performa optimal disarankan export per bulan untuk data >1000 records
- **Timeout:** Export akan timeout setelah 60 detik (untuk data sangat besar)
- **Format:** Hanya 4 format yang didukung (XLSX, CSV, JSON, PDF)
- **Filter:** Hanya bisa filter berdasarkan tahun dan bulan

## Update & Changelog

### Version 2.0 (Current)
- ✨ Perbaikan template Excel dengan styling modern
- ✨ PDF template yang lebih profesional
- ✨ JSON export dengan metadata lengkap
- ✨ CSV dengan format yang lebih baik
- ✨ Error handling yang lebih robust
- ✨ Notifikasi sukses/gagal yang jelas
- ✨ Freeze panes dan auto-filter di Excel
- ✨ Summary statistics di semua format

### Version 1.0
- 📦 Fitur export dasar (XLSX, CSV, JSON, PDF)
- 📦 Filter tahun dan bulan
- 📦 Preview data

## Support

Jika mengalami masalah atau memiliki pertanyaan:
- 📧 Email: support@mcc.go.id
- 📱 WhatsApp: +62 xxx xxxx xxxx
- 🌐 Website: https://mcc.go.id

---

**Dibuat oleh:** Tim IT MCC  
**Terakhir diupdate:** 2 Mei 2026  
**Versi:** 2.0
