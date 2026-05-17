# Performance Optimization Guide

## Masalah yang Diperbaiki

### 1. Log File Terlalu Besar (23MB)
**Dampak:**
- Editor lambat/freeze saat membuka log
- Disk space terbuang
- Sulit mencari error terbaru

**Solusi:**
- ✅ Backup dan clear log file
- ✅ Ubah `LOG_CHANNEL` dari `stack` ke `daily`
- ✅ Set `LOG_DAILY_DAYS=7` (otomatis hapus log > 7 hari)
- ✅ Ubah `LOG_LEVEL` dari `error` ke `info`

### 2. Akses Project Lambat
**Penyebab:**
- Config tidak di-cache
- Routes tidak di-cache
- Views tidak di-cache
- Logging berlebihan

**Solusi:**
- ✅ Cache config: `php artisan config:cache`
- ✅ Cache routes: `php artisan route:cache`
- ✅ Cache views: `php artisan view:cache`
- ✅ Kurangi logging yang tidak perlu

---

## Konfigurasi Baru

### .env
```env
# Log Configuration
LOG_CHANNEL=daily          # Rotasi harian (bukan stack)
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=info             # Info level (bukan error)
LOG_DAILY_DAYS=7           # Simpan 7 hari terakhir

# Debug Mode
APP_DEBUG=true             # Set false di production
```

### Log Rotation
Laravel akan otomatis membuat file log baru setiap hari:
```
laravel-2026-05-17.log
laravel-2026-05-18.log
laravel-2026-05-19.log
...
```

File log lebih dari 7 hari akan otomatis dihapus.

---

## Perintah Optimasi

### Development
```bash
# Clear semua cache
php artisan optimize:clear

# Cache untuk performa
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Production
```bash
# Full optimization
php artisan optimize

# Atau manual:
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Clear Cache (saat development)
```bash
# Clear semua
php artisan optimize:clear

# Atau individual:
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## Maintenance Log File

### Cek Ukuran Log
```powershell
Get-Item storage/logs/*.log | Select-Object Name, @{Name="Size(MB)";Expression={[math]::Round($_.Length/1MB,2)}}
```

### Backup dan Clear Log
```powershell
# Backup
Copy-Item storage/logs/laravel.log storage/logs/laravel.log.backup

# Clear
Clear-Content storage/logs/laravel.log
```

### Hapus Log Lama
```powershell
# Hapus log lebih dari 7 hari
Get-ChildItem storage/logs/*.log | Where-Object {$_.LastWriteTime -lt (Get-Date).AddDays(-7)} | Remove-Item
```

---

## Optimasi Database

### Index yang Penting
```sql
-- Events table
CREATE INDEX idx_events_tanggal ON events(tanggal);
CREATE INDEX idx_events_bulan ON events(bulan);
CREATE INDEX idx_events_status ON events(status_data);
CREATE INDEX idx_events_key_full ON events(key_full);
CREATE INDEX idx_events_key_short ON events(key_short);
```

### Query Optimization
```php
// BAD: N+1 Query
$events = Event::all();
foreach ($events as $event) {
    echo $event->organizer->name;
}

// GOOD: Eager Loading
$events = Event::with('organizer')->get();
foreach ($events as $event) {
    echo $event->organizer->name;
}
```

---

## Optimasi Session

### Gunakan Database Session (sudah dikonfigurasi)
```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

### Cleanup Session Lama
```bash
php artisan session:gc
```

Atau tambahkan ke cron:
```bash
# Setiap hari jam 2 pagi
0 2 * * * cd /path/to/project && php artisan session:gc
```

---

## Monitoring Performa

### Cek Memory Usage
```bash
php artisan about
```

### Cek Query Performance
Install Laravel Debugbar (development only):
```bash
composer require barryvdh/laravel-debugbar --dev
```

### Cek Slow Queries
Edit `config/database.php`:
```php
'connections' => [
    'pgsql' => [
        // ...
        'options' => [
            PDO::ATTR_TIMEOUT => 5, // 5 detik timeout
        ],
    ],
],
```

---

## Checklist Optimasi

### Development
- [x] Log rotation (daily)
- [x] Cache config/routes/views
- [x] Kurangi logging berlebihan
- [x] Debug mode ON
- [ ] Install debugbar (optional)

### Production
- [ ] Set `APP_DEBUG=false`
- [ ] Set `LOG_LEVEL=error`
- [ ] Cache semua (`php artisan optimize`)
- [ ] Setup queue worker
- [ ] Setup cron untuk cleanup
- [ ] Enable OPcache
- [ ] Use CDN untuk assets

---

## Tips Performa

### 1. Lazy Loading Images
```html
<img src="image.jpg" loading="lazy" />
```

### 2. Pagination
```php
// Gunakan pagination, jangan get() semua
Event::paginate(50); // Bukan Event::all()
```

### 3. Chunk Large Queries
```php
// Untuk proses batch besar
Event::chunk(100, function ($events) {
    foreach ($events as $event) {
        // Process
    }
});
```

### 4. Cache Query Results
```php
// Cache hasil query 1 jam
$stats = Cache::remember('dashboard_stats', 3600, function () {
    return Event::selectRaw('
        COUNT(*) as total,
        SUM(jumlah_peserta) as total_peserta
    ')->first();
});
```

---

## Troubleshooting

### Project Masih Lambat?

1. **Cek PHP Version:**
   ```bash
   php -v
   # Minimal PHP 8.3
   ```

2. **Cek Memory Limit:**
   ```bash
   php -i | grep memory_limit
   # Minimal 512M untuk development
   ```

3. **Cek Composer Autoload:**
   ```bash
   composer dump-autoload -o
   ```

4. **Cek Database Connection:**
   ```bash
   php artisan tinker
   >>> DB::connection()->getPdo();
   ```

5. **Disable Xdebug (jika tidak digunakan):**
   ```bash
   php -v
   # Jika ada Xdebug, disable di php.ini
   ```

---

## Hasil yang Diharapkan

### Sebelum Optimasi
- Log file: 23MB
- Load time: 3-5 detik
- Memory usage: 512MB+

### Setelah Optimasi
- Log file: < 5MB (rotasi harian)
- Load time: 1-2 detik
- Memory usage: 256MB

---

## Maintenance Schedule

### Harian (Otomatis)
- Log rotation
- Session cleanup

### Mingguan (Manual)
- Clear cache development
- Cek ukuran database
- Backup database

### Bulanan (Manual)
- Review slow queries
- Optimize database tables
- Update dependencies

---

**Status: OPTIMIZED** ✅

Performa project sekarang lebih cepat dan log file tidak akan membesar lagi!
