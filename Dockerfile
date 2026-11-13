# syntax=docker/dockerfile:1
#
# Production image for Render (or any Docker host).
# Stages: 1) PHP dependencies  2) frontend build (needs vendor/ for Flux CSS)  3) Apache runtime

############################
# 1. Composer dependencies
############################
FROM php:8.4-cli AS vendor

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev libicu-dev \
    && docker-php-ext-install zip intl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts

############################
# 2. Frontend assets (vite-plus + Tailwind)
############################
FROM node:22-bookworm-slim AS assets

WORKDIR /app
COPY package.json .npmrc ./
RUN npm install --no-audit --no-fund

COPY --from=vendor /app /app
RUN npm run build

############################
# 3. Runtime: PHP 8.4 + Apache
############################
FROM php:8.4-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev libicu-dev sqlite3 \
    && docker-php-ext-install zip intl opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html
COPY --from=vendor --chown=www-data:www-data /app /var/www/html
COPY --from=assets --chown=www-data:www-data /app/public/build /var/www/html/public/build

RUN php artisan package:discover --ansi

# Render injects $PORT (default 10000); the entrypoint rewrites Apache's Listen port.
ENV PORT=10000
EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
