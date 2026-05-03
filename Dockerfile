# ============================================================
# Stage 1 — Node.js asset build
# ============================================================
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/

RUN npm run build

# ============================================================
# Stage 2 — Composer dependency install
# ============================================================
FROM composer:2 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --ignore-platform-reqs

# ============================================================
# Stage 3 — Final FrankenPHP image
# ============================================================
FROM dunglas/frankenphp:latest-php8.3

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        libpq-dev \
        unzip \
        git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && install-php-extensions \
        intl \
        gd \
        zip \
        bcmath \
        pdo_pgsql \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy Composer vendor directory from builder stage
COPY --from=composer-builder /app/vendor ./vendor

# Copy compiled frontend assets from node builder stage
COPY --from=node-builder /app/public/build ./public/build

# Copy the full application source
COPY . .

# Ensure storage and bootstrap/cache directories exist with correct permissions
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Write the Caddyfile that serves the Laravel app on port 8080
RUN cat > /etc/caddy/Caddyfile <<'EOF'
{
    # Disable the admin API in production
    admin off
    # Disable automatic HTTPS — Railway handles TLS termination
    auto_https off
}

:8080 {
    # Set the document root to Laravel's public directory
    root * /app/public

    # Enable PHP via FrankenPHP
    php_server

    # Serve static files directly; fall back to index.php
    file_server

    encode gzip

    log {
        output stdout
    }
}
EOF

# Copy and set up the entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
