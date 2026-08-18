# Single-container image for Render's free Web Service tier (see
# docs/deployment.md). Render has no native PHP buildpack, so this bundles
# PHP-FPM and Nginx in one container via a multi-stage build: the frontend
# assets are compiled in a throwaway Node stage, then copied into the final
# PHP runtime image. This is the only Docker image left in the project —
# the local Docker Compose setup was removed (Docker Desktop cannot start on
# the development machine, see Aufgabenstellung, Abschnitt 21, and
# docs/development.md); local development runs natively in WSL2 instead.

FROM node:22-alpine AS frontend

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
        nginx \
        postgresql-dev \
        libzip-dev \
        icu-dev \
        libpng-dev \
        oniguruma-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        zip \
        intl \
        gd \
        bcmath \
        opcache \
    && apk del $PHPIZE_DEPS

COPY docker/render/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/render/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/render/start.sh /start.sh
RUN chmod +x /start.sh

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000

CMD ["/start.sh"]
