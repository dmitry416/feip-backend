#!/bin/sh
set -e

while ! nc -z db 5432; do
  sleep 1
done

cd /var/www/html

echo "Creating migrations directory..."
    mkdir -p migrations

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

php-fpm
