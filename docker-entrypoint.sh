#!/bin/bash
set -e

echo "==> Memeriksa versi PHP runtime..."
php -v

echo "==> Membersihkan cache lama..."
rm -f bootstrap/cache/*.php

echo "==> Membuat folder storage yang diperlukan..."
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/app/livewire-tmp
mkdir -p storage/logs

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache

echo "==> Menunggu database siap..."
sleep 5

echo "==> Menjalankan migrasi database..."
php artisan migrate --force

echo "==> Mendaftarkan ulang semua package..."
php artisan package:discover --ansi

echo "==> Publish assets & cache ikon Filament..."
php artisan filament:assets
php artisan icons:cache

echo "==> Cache konfigurasi & route..."
php artisan config:cache
php artisan route:cache

echo "================================================"
echo "  SiRekap siap diakses!"
echo "  Buka: http://10.10.20.5:8008/admin"
echo "================================================"

exec apache2-foreground
