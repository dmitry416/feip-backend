FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    $PHPIZE_DEPS \
    postgresql-dev \
    libzip-dev \
    icu-dev \
    rabbitmq-c-dev \
    netcat-openbsd \
    bash \
    shadow

RUN echo "http://dl-cdn.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories && \
    apk add --no-cache supervisor

RUN pecl install amqp && docker-php-ext-enable amqp
RUN docker-php-ext-install pdo_pgsql intl zip

RUN mkdir -p /etc/supervisor/conf.d/

COPY supervisor/supervisord.conf /etc/supervisord.conf
COPY supervisor/workers.conf /etc/supervisor/conf.d/workers.conf

RUN which supervisord || echo "supervisord not found"

ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} -S appgroup && \
    adduser -u ${UID} -S appuser -G appgroup

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

WORKDIR /var/www/html

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log /var/log/supervisor && \
    chown -R appuser:appgroup /var/www/html/var /var/log/supervisor && \
    chmod -R 775 /var/www/html/var

USER root

ENTRYPOINT ["docker-entrypoint"]

CMD ["php-fpm"]
