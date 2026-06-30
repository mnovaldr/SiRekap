#!/bin/bash
set -e

echo "==> Membuat folder storage yang diperlukan..."
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/logs
mkdir -p storage/app
mkdir -p bootstrap/cache

chmod -R 775 storage bootstrap/cache

echo "==> Cache konfigurasi..."
php artisan config:cache

echo "==> Cache route..."
php artisan route:cache

echo "==> Cache view..."
php artisan view:cache

echo "==> Cache ikon Filament..."
php artisan icons:cache

echo "==> Storage link..."
php artisan storage:link --force

echo "==> Menjalankan migrasi..."
php artisan migrate --force

echo "==> SiRekap siap!"
exec php artisan serve --host 0.0.0.0 --port "$PORT"
