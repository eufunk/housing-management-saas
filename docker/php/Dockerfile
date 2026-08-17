FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
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
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
