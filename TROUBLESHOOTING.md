# 🔧 Troubleshooting Guide

## Error 500 Server Error (FIXED)

### Penyebab
Route `filament.admin.pages.dashboard` not defined - disebabkan oleh file `OptimizedDashboard.php` yang conflict dengan Filament default.

### Solusi (SUDAH DITERAPKAN)
```bash
# File sudah dihapus:
# app/Filament/Pages/OptimizedDashboard.php

# Cache sudah di-clear dan rebuild
php artisan optimize:clear
php artisan optimize
```

✅ **Status:** Login sudah berfungsi normal

---

## Error 405 Method Not Allowed (FIXED)

### Penyebab Umum
1. **Middleware OptimizeResponse** - GZIP compression bisa mengganggu Livewire/Filament POST requests
2. **CSRF Token Expired** - Session timeout atau cache issue
3. **Route Cache** - Route cache tidak match dengan actual routes
4. **Livewire Issue** - Livewire component tidak ter-register dengan benar

### Solusi

#### 1. Disable OptimizeResponse Middleware (SUDAH DILAKUKAN)
File: `bootstrap/app.php`

Middleware OptimizeResponse sudah di-comment out. Jika masih ada error 405:

```php
// Line ini sudah di-comment:
// $middleware->append(\App\Http\Middleware\OptimizeResponse::class);
```

#### 2. Clear All Cache
```bash
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

#### 3. Check CSRF Token
Jika error terjadi saat submit form:
- Refresh halaman (F5)
- Clear browser cache
- Check `storage/framework/sessions` folder permissions

#### 4. Check Livewire
```bash
php artisan livewire:discover
php artisan optimize
```

#### 5. Enable Middleware Secara Bertahap
Jika ingin enable kembali OptimizeResponse:

**Edit `bootstrap/app.php`:**
```php
// Uncomment line ini:
$middleware->append(\App\Http\Middleware\OptimizeResponse::class);
```

**Lalu test:**
```bash
php artisan optimize:clear
php artisan optimize
```

---

## Error Lainnya

### Slow Performance After Update
```bash
# Clear cache
php artisan cache:clear
php artisan optimize

# Check .env
# Pastikan:
# APP_DEBUG=false
# CACHE_STORE=file
```

### Filter Tidak Reaktif
```bash
# Clear Livewire cache
php artisan optimize:clear
php artisan optimize
```

### Database Query Slow
```bash
# Check indexes
php artisan tinker
DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'events'");

# Should show:
# - events_status_tanggal_idx
# - events_status_primary_tanggal_idx
# - events_status_tanggal_subsektor_idx
# - events_status_bulan_idx
```

### Cache Not Working
```bash
# Test cache
php artisan tinker
Cache::put('test', 'value', 60);
Cache::get('test');  # Should return 'value'

# Check cache driver
config('cache.default');  # Should be 'file'
```

---

## Performance Optimization Status

### ✅ Aktif (Tidak Menyebabkan Error)
- Database composite indexes
- Query caching (Dashboard, AnalitikEkraf, Widgets)
- Dropdown caching
- Query optimization (merge redundant queries)
- `.env` optimization (DEBUG=false, CACHE=file)
- Laravel optimize (config, routes, views cache)

### ⚠️ Disabled (Bisa Menyebabkan Error 405)
- OptimizeResponse Middleware (GZIP compression)

### 🔄 Cara Enable Kembali OptimizeResponse
1. Uncomment di `bootstrap/app.php`
2. Test di browser
3. Jika error 405 muncul lagi, disable kembali

---

## Monitoring

### Check Application Status
```bash
php artisan about
```

### Check Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Nginx/Apache logs (if applicable)
tail -f /var/log/nginx/error.log
```

### Check Performance
1. Open browser DevTools (F12)
2. Go to Network tab
3. Reload page
4. Check:
   - Total load time
   - Number of requests
   - Response sizes

---

## Contact

Jika masih ada masalah:
1. Check `storage/logs/laravel.log`
2. Run `php artisan optimize:clear`
3. Clear browser cache
4. Try incognito/private mode

**Last Updated:** 6 Mei 2026
