FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    $PHPIZE_DEPS \
    postgresql-dev \
    libzip-dev \
    icu-dev

RUN docker-php-ext-install pdo pdo_pgsql intl zip

WORKDIR /var/www/html

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
