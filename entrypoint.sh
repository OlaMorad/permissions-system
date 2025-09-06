#!/bin/sh
set -e

# انتظار MySQL لتكون جاهزة
echo "Waiting for MySQL to be ready..."
until php -r "new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));"
do
  echo "MySQL is unavailable - sleeping"
  sleep 3
done

echo "MySQL is ready - running migrations..."
php artisan migrate --force

echo "Seeding database..."
php artisan db:seed --force

echo "Starting Laravel server..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
