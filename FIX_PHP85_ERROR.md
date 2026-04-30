# Fix Error: Undefined variable $exceptionAsMarkdown

## Masalah

Error ini muncul karena **incompatibility antara Laravel 11.51.0 dan PHP 8.5**.

```
Undefined variable $exceptionAsMarkdown
(View: vendor\laravel\framework\src\Illuminate\Foundation\resources\exceptions\renderer\show.blade.php)
```

## Solusi (Pilih Salah Satu)

### Solusi 1: Update Laravel (RECOMMENDED)

```bash
composer update laravel/framework
```

Ini akan update Laravel ke versi terbaru yang sudah fix bug PHP 8.5.

### Solusi 2: Downgrade PHP ke 8.3

1. Buka **Laragon**
2. Klik **Menu** > **PHP** > **Version**
3. Pilih **PHP 8.3.x** (bukan 8.5.x)
4. Restart Laragon

### Solusi 3: Suppress Deprecation Warnings

Edit `php.ini`:
```ini
; Suppress deprecation warnings
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
```

Lokasi: `C:\laragon\bin\php\php-8.5.4-nts-Win32-vs17-x64\php.ini`

Restart Laragon setelah edit.

### Solusi 4: Temporary Fix (Development Only)

Edit `.env`:
```env
APP_DEBUG=false
```

**WARNING:** Ini akan hide semua error, tidak recommended untuk development.

---

## Rekomendasi

**Gunakan Solusi 2** (Downgrade ke PHP 8.3):
- Paling mudah
- Paling stabil
- Laravel 11 fully compatible dengan PHP 8.3
- PHP 8.5 masih terlalu baru (banyak package belum support)

**Cara Downgrade:**
1. Laragon > Menu > PHP > Version > php-8.3.x
2. Restart Laragon
3. Verify: `php --version` (harus 8.3.x)
4. Refresh browser

---

## Verifikasi Fix

Setelah apply salah satu solusi:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Lalu akses: `http://localhost:8000/admin`

---

## Catatan

- PHP 8.5 baru rilis, banyak package (termasuk Laravel) belum fully support
- Deprecation warning `PDO::MYSQL_ATTR_SSL_CA` akan hilang setelah downgrade ke PHP 8.3
- Untuk production, selalu gunakan PHP LTS (8.3 saat ini)
