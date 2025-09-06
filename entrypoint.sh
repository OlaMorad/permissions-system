#!/bin/sh
set -e

echo "Waiting for MySQL to be ready..."

while ! mysqladmin ping -h "$DB_HOST" -P "$DB_PORT" --silent; do
    echo "MySQL is unavailable - sleeping"
    sleep 3
done

echo "MySQL is ready! Running migrations..."
php artisan db:wipe --force
php artisan migrate:fresh --force

echo "Seeding database..."
php artisan db:seed --force

echo "Starting Laravel server..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
