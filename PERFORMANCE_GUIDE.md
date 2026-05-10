# 🚀 Panduan Optimasi Performa MCC Rekap Backend

## Perubahan yang Sudah Diterapkan

### 1. **Konfigurasi Environment (.env)**
```env
APP_DEBUG=false          # ✅ Disable debug mode (sangat penting!)
LOG_LEVEL=error          # ✅ Hanya log error, bukan debug
CACHE_STORE=file         # ✅ File cache lebih cepat dari database
SESSION_DRIVER=database  # ✅ Database session untuk multi-server
```

### 2. **Database Indexes**
✅ **Composite indexes untuk events table:**
- `(status_data, tanggal)` - Query paling sering
- `(status_data, is_primary_row, tanggal)` - Chart & stats
- `(status_data, tanggal, sub_sektor)` - Filter sub sektor
- `(status_data, bulan)` - Rekap bulanan

✅ **Indexes untuk cache & sessions:**
- `cache.expiration` - Cleanup expired cache
- `sessions.user_id` - User session lookup
- `sessions.last_activity` - Session cleanup
- `activity_logs.created_at` - Widget stats
- `activity_logs.action` - Action filtering

### 3. **Query Optimization**
✅ **Cache dropdown options (30 menit):**
- Dashboard: tahun, bulan, sub sektor
- AnalitikEkraf: tahun

✅ **Cache analytics data (15 menit):**
- AnalitikEkraf: semua chart & tabel

✅ **Cache widget stats (5 menit):**
- ActivityStatsWidget: 4 query → 1 cached result

✅ **Eliminate redundant queries:**
- KomersialChart: 3 query → 1 query
- BidangDistributionChart: 2 query → 1 query
- SubSektorDistributionChart: 2 query → 1 query
- TopJenisKegiatanChart: 2 query → 1 query
- TopLokasiWidget: 2 query → 1 query
- TopOrganisasiWidget: 2 query → 1 query

### 4. **Response Optimization**
✅ **OptimizeResponse Middleware:**
- GZIP compression untuk response > 1KB
- Cache headers untuk static assets (1 tahun)
- Cache headers untuk Filament assets (1 hari)

### 5. **Filter Reactivity**
✅ **AnalitikEkraf filter:**
- Tambah `.live()` pada select year
- Data update tanpa refresh halaman

✅ **Dashboard filter:**
- Cache dropdown queries
- Lazy load widgets

---

## Checklist Optimasi Tambahan

### Server Level (Belum Diterapkan)

#### PHP Configuration (php.ini)
```ini
; OPcache - SANGAT PENTING!
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0  ; Production only!
opcache.revalidate_freq=0

; Memory & Execution
memory_limit=512M
max_execution_time=60
upload_max_filesize=20M
post_max_size=25M

; Realpath Cache
realpath_cache_size=4096K
realpath_cache_ttl=600
```

#### Web Server (Nginx)
```nginx
# Gzip Compression
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css text/xml text/javascript 
           application/x-javascript application/xml+rss 
           application/json application/javascript;

# Browser Caching
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}

# FastCGI Cache (optional)
fastcgi_cache_path /var/cache/nginx levels=1:2 
                   keys_zone=PHPCACHE:100m inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";
```

### Application Level

#### Composer Optimization
```bash
composer install --optimize-autoloader --no-dev
composer dump-autoload --optimize --classmap-authoritative
```

#### Laravel Optimization
```bash
# Production deployment
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

# Clear cache jika ada perubahan
php artisan optimize:clear
```

#### Asset Optimization
```bash
# Build production assets
npm run build

# Atau dengan Vite optimization
npm run build -- --minify
```

---

## Monitoring & Debugging

### Check Query Performance
```bash
# Enable query log temporarily
php artisan tinker
DB::enableQueryLog();
# ... run your code ...
dd(DB::getQueryLog());
```

### Check Cache Hit Rate
```bash
php artisan tinker
Cache::get('test_key'); // Check if cache working
```

### Check OPcache Status
```php
<?php
// Create file: public/opcache-status.php
phpinfo(INFO_GENERAL);
// Visit: your-domain.com/opcache-status.php
```

### Laravel Telescope (Development Only)
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

---

## Performance Benchmarks

### Before Optimization
- Dashboard load: ~3-5 seconds
- AnalitikEkraf load: ~4-6 seconds
- Filter change: Requires page refresh
- Total queries per page: 20-30 queries

### After Optimization (Expected)
- Dashboard load: ~0.5-1.5 seconds (first load)
- Dashboard load: ~0.2-0.5 seconds (cached)
- AnalitikEkraf load: ~0.8-2 seconds (first load)
- AnalitikEkraf load: ~0.3-0.8 seconds (cached)
- Filter change: Instant (no refresh)
- Total queries per page: 5-10 queries (with cache)

---

## Troubleshooting

### Cache Not Working?
```bash
# Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan optimize:clear

# Check cache driver
php artisan tinker
echo config('cache.default'); // Should be 'file'
```

### Still Slow?
1. Check database connection (use pooler, not direct)
2. Check network latency (ngrok adds ~100-300ms)
3. Check browser DevTools Network tab
4. Enable Laravel Debugbar (dev only)
5. Check server resources (CPU, RAM, disk I/O)

### Indexes Not Applied?
```bash
# Check indexes
php artisan tinker
DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'events'");
```

---

## Maintenance Commands

### Daily
```bash
# Clear expired cache & sessions
php artisan cache:prune-stale-tags
php artisan session:gc
```

### Weekly
```bash
# Optimize database
php artisan db:optimize
```

### After Code Changes
```bash
php artisan optimize:clear
php artisan optimize
php artisan filament:optimize
```

---

## Additional Tips

1. **Use CDN** untuk static assets (CSS, JS, images)
2. **Enable HTTP/2** di web server
3. **Use Redis** untuk cache & sessions (production)
4. **Database Connection Pooling** - sudah aktif (Supabase pooler)
5. **Lazy Load Images** di blade templates
6. **Pagination** untuk large datasets
7. **Background Jobs** untuk heavy operations (import/export)
8. **Database Query Optimization** - use EXPLAIN ANALYZE
9. **Monitor Slow Queries** - enable PostgreSQL slow query log
10. **Regular Maintenance** - VACUUM, ANALYZE tables

---

## Contact & Support

Jika masih ada masalah performa:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check database slow queries
3. Use Laravel Telescope untuk debugging
4. Profile dengan Xdebug (development only)

**Remember:** Performa terbaik = Code optimization + Server optimization + Database optimization
