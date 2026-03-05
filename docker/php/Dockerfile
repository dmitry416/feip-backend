FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    $PHPIZE_DEPS \
    postgresql-dev \
    libzip-dev \
    icu-dev \
    rabbitmq-c-dev \
    netcat-openbsd

RUN pecl install amqp && docker-php-ext-enable amqp

RUN docker-php-ext-install pdo pdo_pgsql intl zip

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

WORKDIR /var/www/html

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

ENTRYPOINT ["docker-entrypoint"]
