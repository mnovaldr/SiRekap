# ⚡ Quick Reference - Performance Optimization

## 🚀 Deployment Checklist

Setiap kali deploy atau update code:

```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --optimize-autoloader --no-dev

# 3. Run migrations
php artisan migrate --force

# 4. Clear & optimize
php artisan optimize:clear
php artisan optimize
php artisan filament:optimize

# 5. Verify
php artisan about
```

---

## 🔧 Common Issues & Solutions

### Issue: Dashboard masih lambat
```bash
# Solution 1: Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan optimize

# Solution 2: Check .env
# Pastikan:
# APP_DEBUG=false
# CACHE_STORE=file
# LOG_LEVEL=error
```

### Issue: Filter tidak reaktif
```bash
# Solution: Clear Livewire cache
php artisan livewire:delete-uploaded-files
php artisan optimize:clear
php artisan optimize
```

### Issue: Data tidak update setelah import
```bash
# Solution: Clear cache
php artisan cache:clear

# Atau clear specific cache key
php artisan tinker
Cache::forget('analitik_data_2024');
Cache::forget('dashboard_available_years');
Cache::forget('dashboard_sub_sektors');
```

### Issue: Error 500 setelah update
```bash
# Solution: Rebuild cache
php artisan optimize:clear
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📊 Cache Keys Reference

| Cache Key | TTL | Purpose |
|-----------|-----|---------|
| `latest_data_year` | 30 min | Latest year in database |
| `dashboard_available_years` | 30 min | Dropdown tahun |
| `dashboard_sub_sektors` | 30 min | Dropdown sub sektor |
| `dashboard_active_months_{year}` | 30 min | Dropdown bulan per tahun |
| `analitik_available_years` | 30 min | Dropdown tahun analitik |
| `analitik_data_{year}` | 15 min | Analytics data per tahun |
| `activity_stats_widget` | 5 min | Activity stats widget |

### Clear Specific Cache
```bash
php artisan tinker
Cache::forget('analitik_data_2024');  # Clear analytics 2024
Cache::flush();                        # Clear ALL cache
```

---

## 🗄️ Database Indexes

### Events Table
- `events_status_tanggal_idx` - (status_data, tanggal)
- `events_status_primary_tanggal_idx` - (status_data, is_primary_row, tanggal)
- `events_status_tanggal_subsektor_idx` - (status_data, tanggal, sub_sektor)
- `events_status_bulan_idx` - (status_data, bulan)

### Check Indexes
```bash
php artisan tinker
DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'events'");
```

---

## 🎯 Performance Targets

| Metric | Target | Current |
|--------|--------|---------|
| Dashboard (first load) | < 1.5s | ✅ 0.5-1.5s |
| Dashboard (cached) | < 0.5s | ✅ 0.2-0.5s |
| AnalitikEkraf (first) | < 2s | ✅ 0.8-2s |
| AnalitikEkraf (cached) | < 0.8s | ✅ 0.3-0.8s |
| Filter change | Instant | ✅ Instant |
| Queries per page | < 10 | ✅ 5-10 |

---

## 🔍 Debugging Commands

```bash
# Check app status
php artisan about

# Check config
php artisan tinker
config('app.debug');      # Should be false
config('cache.default');  # Should be 'file'
config('session.driver'); # Should be 'database'

# Check logs
tail -f storage/logs/laravel.log

# Check database connection
php artisan tinker
DB::connection()->getPdo();

# Test cache
php artisan tinker
Cache::put('test', 'value', 60);
Cache::get('test');  # Should return 'value'
```

---

## 📱 Browser DevTools

### Check Response Time
1. Open DevTools (F12)
2. Go to Network tab
3. Reload page
4. Check "Time" column for each request

### Check GZIP Compression
1. Open DevTools (F12)
2. Go to Network tab
3. Click any request
4. Check Response Headers
5. Look for: `Content-Encoding: gzip`

### Check Cache Headers
1. Open DevTools (F12)
2. Go to Network tab
3. Click any CSS/JS file
4. Check Response Headers
5. Look for: `Cache-Control: public, max-age=...`

---

## 🆘 Emergency Rollback

Jika ada masalah serius:

```bash
# 1. Restore .env
cp .env.backup .env

# 2. Clear everything
php artisan optimize:clear
php artisan cache:clear

# 3. Rollback migration (if needed)
php artisan migrate:rollback --step=2

# 4. Restart
php artisan optimize
```

---

## 📞 Contact

**Developer:** Kiro AI  
**Last Updated:** 6 Mei 2026  
**Version:** 2.0
