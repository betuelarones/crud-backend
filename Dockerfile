# syntax=docker/dockerfile:1.7

FROM php:8.4-fpm-alpine AS vendor

RUN apk add --no-cache unzip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-scripts \
    --optimize-autoloader

COPY . .
RUN composer dump-autoload --no-dev --optimize --no-interaction

FROM php:8.4-fpm-alpine AS runtime

RUN docker-php-ext-install -j"$(nproc)" bcmath opcache pdo_mysql

WORKDIR /var/www/html

COPY --from=vendor /app /var/www/html
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    && mkdir -p \
        bootstrap/cache \
        storage/api-docs \
        storage/app \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
