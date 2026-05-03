#!/bin/sh
set -e

# ---------------------------------------------------------------------------
# Laravel bootstrap entrypoint for Railway / FrankenPHP
# ---------------------------------------------------------------------------

APP_DIR="/app"

cd "$APP_DIR"

# 1. Generate application key if not already set
if [ -z "$APP_KEY" ]; then
    echo "[entrypoint] Generating application key..."
    php artisan key:generate --force
fi

# 2. Run database migrations
echo "[entrypoint] Running database migrations..."
php artisan migrate --force

# 3. Cache Laravel configuration for production performance
echo "[entrypoint] Caching Laravel configuration..."
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

# 4. Ensure storage symlink exists
echo "[entrypoint] Linking storage..."
php artisan storage:link --force 2>/dev/null || true

# 5. Fix permissions one final time (in case volumes were mounted)
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# 6. Start FrankenPHP
echo "[entrypoint] Starting FrankenPHP on 0.0.0.0:8080..."
exec frankenphp run --config /etc/caddy/Caddyfile
