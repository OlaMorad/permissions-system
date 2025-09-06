#!/bin/sh
set -e

echo "Starting Laravel container..."

# تأكد من وجود .env
if [ ! -f .env ]; then
    cp .env.example .env
fi

echo "Running migrations..."
php artisan migrate:fresh --force

echo "Seeding database..."
php artisan db:seed --force

echo "Starting Laravel server..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
