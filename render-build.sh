#!/usr/bin/env bash
# exit on error
set -o errexit

echo "==> Menginstall dependensi PHP (Composer)..."
composer install --optimize-autoloader --no-dev

echo "==> Menginstall dependensi Node.js & Build assets..."
npm install
npm run build

echo "==> Menyiapkan direktori storage..."
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/logs
mkdir -p storage/app/livewire-tmp
mkdir -p bootstrap/cache
chmod -R 777 storage bootstrap/cache

echo "==> Clear & Cache config, routes, views..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache

echo "==> Menjalankan migrasi database..."
php artisan migrate --force

echo "==> Membuat storage link..."
php artisan storage:link --force

echo "==> Build Selesai!"
