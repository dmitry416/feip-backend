#!/bin/sh
set -e

while ! nc -z db 5432; do
  sleep 1
done
echo "Database is ready!"

while ! nc -z rabbitmq 5672; do
  sleep 1
done
echo "RabbitMQ is ready!"

cd /var/www/html

mkdir -p /var/www/html/var/cache /var/www/html/var/log
rm -f /var/www/html/var/log/dev.log
touch /var/www/html/var/log/dev.log
chmod 666 /var/www/html/var/log/dev.log
chmod -R 777 /var/www/html/var


echo "Creating migrations directory..."
mkdir -p migrations

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "Starting supervisor..."
/usr/bin/supervisord -c /etc/supervisord.conf -n &
sleep 3


echo "Starting PHP-FPM..."
exec php-fpm -F
