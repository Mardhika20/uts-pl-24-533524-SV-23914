
FROM dunglas/frankenphp:1.4-php8.3

WORKDIR /app

# Install ekstensi PHP yang dibutuhkan Laravel
RUN install-php-extensions \
    pdo_mysql \
    pdo_sqlite \
    zip \
    intl

# Mengambil Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Menyalin konfigurasi Composer
COPY composer.json composer.lock ./

# Menginstall dependency Laravel
RUN composer install --no-dev --no-interaction \
    --prefer-dist --no-progress --no-scripts

# Menyalin seluruh source code Laravel
COPY . .

# Konfigurasi FrankenPHP / Caddy
COPY Caddyfile /etc/frankenphp/Caddyfile

# Optimasi autoload dan membuat direktori Laravel
RUN composer dump-autoload --optimize \
    && mkdir -p storage/framework/cache \
       storage/framework/sessions \
       storage/framework/views \
       storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ==========================================
# PERBAIKAN STORAGE GAMBAR RESEP
# ==========================================

# Membuat symbolic link dari public/storage
# ke storage/app/public secara otomatis
RUN php artisan storage:link

# ==========================================
# KONFIGURASI SERVER
# ==========================================

ENV SERVER_NAME=:8000
ENV CADDY_SERVER_ROOT=/app/public

EXPOSE 8000

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
