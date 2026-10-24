
FROM dunglas/frankenphp:1.4-php8.3

WORKDIR /app

RUN install-php-extensions \
    pdo_mysql \
    pdo_sqlite \
    zip \
    intl

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-interaction \
    --prefer-dist --no-progress --no-scripts

COPY . .

COPY Caddyfile /etc/frankenphp/Caddyfile

RUN composer dump-autoload --optimize \
    && mkdir -p storage/framework/cache \
       storage/framework/sessions \
       storage/framework/views \
       storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV SERVER_NAME=:8000
ENV CADDY_SERVER_ROOT=/app/public

EXPOSE 8000

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
