#!/bin/sh
set -e

echo "Checking environment variables..."
: "${DB_HOST:?DB_HOST is required}"
: "${DB_PORT:?DB_PORT is required}"
: "${DB_USERNAME:?DB_USERNAME is required}"
: "${DB_PASSWORD:?DB_PASSWORD is required}"
: "${PORT:?PORT is required}"

echo "Waiting for MySQL to be ready..."
while ! mysqladmin ping -h "$DB_HOST" -P "$DB_PORT" --silent; do
    echo "MySQL is unavailable - sleeping 3s..."
    sleep 3
done

echo "MySQL is ready! Preparing database..."
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "SET FOREIGN_KEY_CHECKS=0;"

echo "Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

echo "Running migrations and seeders..."
php artisan migrate:fresh --seed --force

mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "SET FOREIGN_KEY_CHECKS=1;"

echo "Starting Laravel server..."
exec php -S 0.0.0.0:$PORT -t public
